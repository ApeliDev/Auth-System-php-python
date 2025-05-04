import hashlib
import os
import re
import time
import json
import mysql.connector
from typing import Dict, Any, Optional, Tuple, List


class AuthHelper:
    """Python helper class for the PHP authentication system"""
    
    def __init__(self, host: str = 'localhost', user: str = 'root', password: str = '', database: str = 'auth_system'):
        """Initialize the authentication helper with database connection"""
        self.db_config = {
            'host': host,
            'user': user,
            'password': password,
            'database': database
        }
    
    def _get_connection(self) -> mysql.connector.connection.MySQLConnection:
        """Get a database connection"""
        return mysql.connector.connect(**self.db_config)
    
    def validate_password_strength(self, password: str) -> Tuple[bool, str]:
        """
        Validate password strength
        Returns: (is_valid, message)
        """
        if len(password) < 8:
            return False, "Password must be at least 8 characters long"
        
        # Check for at least one uppercase letter
        if not re.search(r'[A-Z]', password):
            return False, "Password must contain at least one uppercase letter"
        
        # Check for at least one lowercase letter
        if not re.search(r'[a-z]', password):
            return False, "Password must contain at least one lowercase letter"
        
        # Check for at least one digit
        if not re.search(r'\d', password):
            return False, "Password must contain at least one digit"
        
        # Check for at least one special character
        if not re.search(r'[!@#$%^&*(),.?":{}|<>]', password):
            return False, "Password must contain at least one special character"
        
        return True, "Password meets strength requirements"
    
    def analyze_login_patterns(self, user_id: int = None, days: int = 30) -> Dict[str, Any]:
        """
        Analyze login patterns for security monitoring
        Returns statistics about login attempts
        """
        conn = self._get_connection()
        cursor = conn.cursor(dictionary=True)
        
        result = {
            'total_attempts': 0,
            'successful_logins': 0,
            'failed_attempts': 0,
            'unusual_ips': [],
            'login_times': {}
        }
        
        # Get total login attempts
        query = """
        SELECT COUNT(*) as count
        FROM login_attempts
        WHERE attempted_at > DATE_SUB(NOW(), INTERVAL %s DAY)
        """
        params = [days]
        
        if user_id:
            query += " AND username IN (SELECT username FROM users WHERE id = %s)"
            params.append(user_id)
        
        cursor.execute(query, params)
        data = cursor.fetchone()
        result['total_attempts'] = data['count']
        
        # Get successful logins
        query = """
        SELECT COUNT(*) as count
        FROM sessions
        WHERE created_at > DATE_SUB(NOW(), INTERVAL %s DAY)
        """
        params = [days]
        
        if user_id:
            query += " AND user_id = %s"
            params.append(user_id)
        
        cursor.execute(query, params)
        data = cursor.fetchone()
        result['successful_logins'] = data['count']
        
        # Calculate failed attempts
        result['failed_attempts'] = result['total_attempts'] - result['successful_logins']
        
        # Get unusual IPs (IPs used less than 3 times)
        query = """
        SELECT ip_address, COUNT(*) as count
        FROM login_attempts
        WHERE attempted_at > DATE_SUB(NOW(), INTERVAL %s DAY)
        """
        params = [days]
        
        if user_id:
            query += " AND username IN (SELECT username FROM users WHERE id = %s)"
            params.append(user_id)
        
        query += " GROUP BY ip_address HAVING count < 3"
        
        cursor.execute(query, params)
        unusual_ips = cursor.fetchall()
        result['unusual_ips'] = [ip['ip_address'] for ip in unusual_ips]
        
        # Get login time patterns (hour of day)
        query = """
        SELECT HOUR(attempted_at) as hour, COUNT(*) as count
        FROM login_attempts
        WHERE attempted_at > DATE_SUB(NOW(), INTERVAL %s DAY)
        """
        params = [days]
        
        if user_id:
            query += " AND username IN (SELECT username FROM users WHERE id = %s)"
            params.append(user_id)
        
        query += " GROUP BY HOUR(attempted_at)"
        
        cursor.execute(query, params)
        hour_data = cursor.fetchall()
        
        for hour in hour_data:
            result['login_times'][hour['hour']] = hour['count']
        
        cursor.close()
        conn.close()
        
        return result
    
    def export_user_data(self, user_id: int) -> Dict[str, Any]:
        """
        Export user data (for GDPR compliance)
        """
        conn = self._get_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Get user data
        cursor.execute(
            """
            SELECT id, username, email, created_at, last_login
            FROM users
            WHERE id = %s
            """, 
            [user_id]
        )
        
        user = cursor.fetchone()
        if not user:
            cursor.close()
            conn.close()
            return {'error': 'User not found'}
        
        # Get session data
        cursor.execute(
            """
            SELECT session_id, created_at, expires_at, ip_address, user_agent
            FROM sessions
            WHERE user_id = %s
            ORDER BY created_at DESC
            """, 
            [user_id]
        )
        
        sessions = cursor.fetchall()
        
        # Get login attempts
        cursor.execute(
            """
            SELECT ip_address, attempted_at
            FROM login_attempts
            WHERE username = %s
            ORDER BY attempted_at DESC
            """, 
            [user['username']]
        )
        
        login_attempts = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        # Format for export
        user_data = {
            'user_info': user,
            'sessions': sessions,
            'login_attempts': login_attempts
        }
        
        return user_data
    
    def generate_auth_report(self, days: int = 30) -> Dict[str, Any]:
        """
        Generate authentication system report
        """
        conn = self._get_connection()
        cursor = conn.cursor(dictionary=True)
        
        report = {
            'users': {
                'total': 0,
                'active_last_month': 0,
                'new_last_month': 0
            },
            'sessions': {
                'total_active': 0,
                'average_per_user': 0
            },
            'login_attempts': {
                'total': 0,
                'success_rate': 0,
                'failed_rate': 0
            },
            'most_active_hours': [],
            'generated_at': time.strftime('%Y-%m-%d %H:%M:%S')
        }
        
        # Count all users
        cursor.execute("SELECT COUNT(*) as count FROM users")
        report['users']['total'] = cursor.fetchone()['count']
        
        # Count active users in last month
        cursor.execute(
            """
            SELECT COUNT(DISTINCT user_id) as count
            FROM sessions
            WHERE created_at > DATE_SUB(NOW(), INTERVAL %s DAY)
            """,
            [days]
        )
        report['users']['active_last_month'] = cursor.fetchone()['count']
        
        # Count new users in last month
        cursor.execute(
            """
            SELECT COUNT(*) as count
            FROM users
            WHERE created_at > DATE_SUB(NOW(), INTERVAL %s DAY)
            """,
            [days]
        )
        report['users']['new_last_month'] = cursor.fetchone()['count']
        
        # Count active sessions
        cursor.execute(
            """
            SELECT COUNT(*) as count
            FROM sessions
            WHERE expires_at > NOW()
            """
        )
        report['sessions']['total_active'] = cursor.fetchone()['count']
        
        # Calculate average sessions per user
        if report['users']['active_last_month'] > 0:
            report['sessions']['average_per_user'] = report['sessions']['total_active'] / report['users']['active_last_month']
        
        # Login attempt statistics
        cursor.execute(
            """
            SELECT COUNT(*) as count
            FROM login_attempts
            WHERE attempted_at > DATE_SUB(NOW(), INTERVAL %s DAY)
            """,
            [days]
        )
        total_attempts = cursor.fetchone()['count']
        report['login_attempts']['total'] = total_attempts
        
        # Successful logins
        cursor.execute(
            """
            SELECT COUNT(*) as count
            FROM sessions
            WHERE created_at > DATE_SUB(NOW(), INTERVAL %s DAY)
            """,
            [days]
        )
        successful_logins = cursor.fetchone()['count']
        
        # Calculate success and failure rates
        if total_attempts > 0:
            report['login_attempts']['success_rate'] = round((successful_logins / total_attempts) * 100, 2)
            report['login_attempts']['failed_rate'] = round(100 - report['login_attempts']['success_rate'], 2)
        
        # Most active hours
        cursor.execute(
            """
            SELECT HOUR(attempted_at) as hour, COUNT(*) as count
            FROM login_attempts
            WHERE attempted_at > DATE_SUB(NOW(), INTERVAL %s DAY)
            GROUP BY HOUR(attempted_at)
            ORDER BY count DESC
            LIMIT 5
            """,
            [days]
        )
        report['most_active_hours'] = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return report

    def detect_suspicious_activity(self, user_id: int = None) -> List[Dict[str, Any]]:
        """
        Detect suspicious login activity
        """
        conn = self._get_connection()
        cursor = conn.cursor(dictionary=True)
        
        suspicious_activities = []
        
        # Detect multiple failed login attempts
        query = """
        SELECT username, ip_address, COUNT(*) as attempts, 
               MIN(attempted_at) as first_attempt, 
               MAX(attempted_at) as last_attempt
        FROM login_attempts
        WHERE attempted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        """
        
        params = []
        if user_id:
            query += " AND username IN (SELECT username FROM users WHERE id = %s)"
            params.append(user_id)
        
        query += " GROUP BY username, ip_address HAVING attempts >= 5"
        
        cursor.execute(query, params)
        brute_force_attempts = cursor.fetchall()
        
        for attempt in brute_force_attempts:
            suspicious_activities.append({
                'type': 'brute_force',
                'username': attempt['username'],
                'ip_address': attempt['ip_address'],
                'attempts': attempt['attempts'],
                'first_attempt': attempt['first_attempt'],
                'last_attempt': attempt['last_attempt'],
                'risk_level': 'high'
            })
        
        # Detect logins from unusual locations (multiple IPs in short time)
        query = """
        SELECT u.username, COUNT(DISTINCT s.ip_address) as ip_count
        FROM users u
        JOIN sessions s ON u.id = s.user_id
        WHERE s.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        """
        
        params = []
        if user_id:
            query += " AND u.id = %s"
            params.append(user_id)
        
        query += " GROUP BY u.username HAVING ip_count >= 3"
        
        cursor.execute(query, params)
        multi_ip_logins = cursor.fetchall()
        
        for login in multi_ip_logins:
            # Get the IPs used
            cursor.execute(
                """
                SELECT DISTINCT ip_address
                FROM sessions
                WHERE user_id = (SELECT id FROM users WHERE username = %s)
                AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                """,
                [login['username']]
            )
            ips = [row['ip_address'] for row in cursor.fetchall()]
            
            suspicious_activities.append({
                'type': 'multiple_locations',
                'username': login['username'],
                'ip_count': login['ip_count'],
                'ip_addresses': ips,
                'risk_level': 'medium'
            })
        
        # Detect account access at unusual hours
        query = """
        SELECT u.username, HOUR(s.created_at) as hour
        FROM users u
        JOIN sessions s ON u.id = s.user_id
        WHERE s.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND (HOUR(s.created_at) >= 0 AND HOUR(s.created_at) < 5)
        """
        
        params = []
        if user_id:
            query += " AND u.id = %s"
            params.append(user_id)
        
        cursor.execute(query, params)
        unusual_hours = cursor.fetchall()
        
        for login in unusual_hours:
            suspicious_activities.append({
                'type': 'unusual_hours',
                'username': login['username'],
                'hour': login['hour'],
                'risk_level': 'low'
            })
        
        cursor.close()
        conn.close()
        
        return suspicious_activities