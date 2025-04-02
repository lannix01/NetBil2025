

# Project Documentation

## Overview
This project is a web-based application designed for managing users, devices, logs, and administrative tasks. It integrates with MikroTik devices and provides tools for network management, user operations, and system monitoring.

---

## File Structure and Functions

### Root Files

#### `add_user`
- **Function**: Handles the addition of new users to the system.
- **Details**: Processes user input, validates data, and stores the new user in the database or JSON file.

#### `admins`
- **Function**: Stores admin user data in JSON format.
- **Details**: Contains information about administrators, such as usernames and hashed passwords.

#### api
- **Function**: Acts as the main entry point for API-related operations.
- **Details**: Routes API requests to appropriate handlers for user, device, or log management.

#### `auth`
- **Function**: Manages user authentication.
- **Details**: Handles login validation, session management, and access control.

#### `dashboard`
- **Function**: Displays the main dashboard for administrators.
- **Details**: Provides an overview of system statistics, user activity, and device status.

#### `delete_user`
- **Function**: Handles the deletion of users from the system.
- **Details**: Validates user permissions and removes user data from storage.

#### `devices`
- **Function**: Manages device-related operations.
- **Details**: Displays a list of devices, allows adding or removing devices, and integrates with MikroTik APIs.

#### `dhcp`
- **Function**: Handles DHCP-related functionality.
- **Details**: Manages IP address allocation and DHCP server configurations.

#### `fetch_logs`
- **Function**: Fetches logs for display or processing.
- **Details**: Retrieves system logs from JSON files or other storage mechanisms.

#### `fetchUsersCount`
- **Function**: Retrieves the count of registered users.
- **Details**: Queries the database or JSON file to return the total number of users.

#### `footer`
- **Function**: Contains the footer layout for the application.
- **Details**: Provides a consistent footer design across all pages.

#### `get_logs`
- **Function**: Retrieves logs from the system.
- **Details**: Fetches specific logs based on filters or parameters provided by the user.

#### `get_mikrotik_data`
- **Function**: Fetches data from MikroTik devices.
- **Details**: Uses MikroTik APIs to retrieve device configurations, status, or logs.

#### `header`
- **Function**: Contains the header layout for the application.
- **Details**: Provides a consistent header design across all pages.

#### `index`
- **Function**: Main entry point for the application.
- **Details**: Serves as the landing page or login page for the system.

#### `logout`
- **Function**: Handles user logout functionality.
- **Details**: Destroys user sessions and redirects to the login page.

#### `logs`
- **Function**: Displays system logs.
- **Details**: Provides a user interface for viewing and filtering logs.

#### `mikotik-api`
- **Function**: Contains API functions for interacting with MikroTik devices.
- **Details**: Implements methods for connecting to and managing MikroTik routers.

#### `mikrotiktest`
- **Function**: Test script for MikroTik API integration.
- **Details**: Verifies connectivity and functionality of MikroTik API methods.

#### `modify_user`
- **Function**: Handles user modification functionality.
- **Details**: Allows updating user details such as name, email, or permissions.

#### `navbar`
- **Function**: Contains the navigation bar layout.
- **Details**: Provides a consistent navigation menu across all pages.

#### `phpinfo`
- **Function**: Displays PHP configuration information.
- **Details**: Outputs server and PHP environment details for debugging purposes.

#### `ping`
- **Function**: Implements a ping utility.
- **Details**: Sends ICMP requests to test network connectivity.

#### `reports`
- **Function**: Generates and displays reports.
- **Details**: Creates summaries of user activity, device status, or system logs.

#### `routeros_api.class`
- **Function**: A class for interacting with MikroTik RouterOS.
- **Details**: Provides methods for managing RouterOS configurations and retrieving data.

#### `RouterOS`
- **Function**: Another script for RouterOS-related operations.
- **Details**: Implements additional functionality for MikroTik RouterOS management.

#### `sidebar`
- **Function**: Contains the sidebar layout.
- **Details**: Provides a consistent sidebar design for navigation.

#### `system_logs`
- **Function**: Stores system logs in JSON format.
- **Details**: Contains records of system events, errors, and user actions.

#### `tinyfilemanager`
- **Function**: A file manager script.
- **Details**: Allows administrators to upload, download, and manage files on the server.

#### `unblock_user`
- **Function**: Handles unblocking of users.
- **Details**: Removes restrictions or bans on user accounts.

#### `user_operations`
- **Function**: Contains user-related operations.
- **Details**: Implements backend logic for user management tasks.

#### `users`
- **Function**: Displays and manages user data.
- **Details**: Provides a user interface for viewing, adding, editing, or deleting users.

---

## Key Features
1. **User Management**: Add, delete, modify, and unblock users.
2. **Device Management**: Manage devices and interact with MikroTik devices.
3. **Logs and Reports**: Fetch, display, and generate system logs and reports.
4. **Authentication**: Secure login and logout functionality.
5. **File Management**: Includes a file manager for handling files.
6. **Network Utilities**: Implements tools like ping and DHCP management.

---

## Setup Instructions
1. **Server Requirements**:
   - PHP 7.x or higher.
   - A web server (e.g., Apache or Nginx).
   - MySQL or JSON-based storage (depending on the implementation).

2. **Installation**:
   - Place the project files in the web server's root directory.
   - Configure database or JSON files as needed.
   - Update any configuration files (e.g., database credentials, API keys).

3. **Access**:
   - Open `index` in a browser to access the application.

---
