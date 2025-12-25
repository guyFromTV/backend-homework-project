# 🚗 Driving Experience Tracker

A web application for tracking and analyzing driving experiences, built with PHP and MySQL. This project allows users to log their driving sessions with detailed information about conditions, distances, and maneuvers, then visualize the data through an interactive dashboard.

## Features

### Core Functionality
- **User Authentication**: Secure registration and login system with session management
- **Experience Logging**: Record driving sessions with:
  - Date and time
  - Distance traveled (kilometers)
  - Weather conditions
  - Road conditions
  - Traffic levels
  - Multiple maneuvers (many-to-many relationship)
  - Optional comments
- **Dashboard Analytics**: Visual analytics with Chart.js including:
  - Total kilometers and trip count
  - Kilometers by weather conditions
  - Kilometers by road conditions
  - Kilometers by traffic levels
  - Top maneuvers frequency
  - Last 10 experiences timeline
- **Variable Management**: Manage categorical data (weather, road conditions, traffic levels, maneuvers) with activate/deactivate functionality
- **Experience Summary**: Comprehensive list of all driving experiences with DataTables for sorting and searching

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL with PDO
- **Frontend**: 
  - Vanilla JavaScript
  - Chart.js 4.4.1 for data visualization
  - jQuery 3.7.1
  - DataTables 1.13.8 for table management
- **CSS**: Custom styling with CSS variables
- **Security**: 
  - Password hashing with `password_hash()`
  - Prepared statements for SQL injection prevention
  - HTML escaping with `htmlspecialchars()`
  - Session-based authentication

## File Structure

```
.
├── index.php                # Entry point (redirects to dashboard)
├── config.php               # Database and authentication configuration
├── db.php                   # Database connection and utility functions
├── auth.php                 # Authentication functions
├── layout.php               # Common HTML header and footer
│
├── dashboard.php            # Main dashboard with analytics
├── experiences.php          # List all driving experiences
├── experience_add.php       # Form to add new experience
├── experience_save.php      # Process and save new experience
│
├── variables.php            # Manage categorical variables
├── variable_toggle.php      # Toggle active/inactive status
│
├── login.php                # Login page
├── register.php             # Registration page
├── logout.php               # Logout handler
│
├── app.css                  # Main stylesheet
└── app.js                   # Frontend JavaScript
```

## Database Schema

The application requires the following MySQL tables:

### Users Table
```sql
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL
);
```

### Categorical Tables
```sql
CREATE TABLE weather (
    id_weather INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    is_active TINYINT DEFAULT 1
);

CREATE TABLE road_conditions (
    id_road INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    is_active TINYINT DEFAULT 1
);

CREATE TABLE traffic_levels (
    id_traffic INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    is_active TINYINT DEFAULT 1
);

CREATE TABLE maneuvers (
    id_maneuver INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    is_active TINYINT DEFAULT 1
);
```

### Driving Experiences
```sql
CREATE TABLE driving_experiences (
    id_experience INT PRIMARY KEY AUTO_INCREMENT,
    started_at DATETIME NOT NULL,
    km DECIMAL(10,2) NOT NULL,
    id_weather INT NOT NULL,
    id_road INT NOT NULL,
    id_traffic INT NOT NULL,
    comment TEXT,
    FOREIGN KEY (id_weather) REFERENCES weather(id_weather),
    FOREIGN KEY (id_road) REFERENCES road_conditions(id_road),
    FOREIGN KEY (id_traffic) REFERENCES traffic_levels(id_traffic)
);

CREATE TABLE experience_maneuvers (
    id_experience INT NOT NULL,
    id_maneuver INT NOT NULL,
    PRIMARY KEY (id_experience, id_maneuver),
    FOREIGN KEY (id_experience) REFERENCES driving_experiences(id_experience),
    FOREIGN KEY (id_maneuver) REFERENCES maneuvers(id_maneuver)
);
```

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd backend-homework-project
   ```

2. **Configure database**
   - Create a MySQL database
   - Run the SQL schema (see Database Schema section)
   - Update `config.php` with your database credentials:
     ```php
     return [
       'db' => [
         'host' => 'your-host',
         'name' => 'your-database',
         'user' => 'your-username',
         'pass' => 'your-password',
         'charset' => 'utf8mb4',
       ],
       'auth' => [
         'enabled' => true,
       ],
     ];
     ```

3. **Set up web server**
   - Point document root to the project directory
   - Ensure PHP is properly configured
   - Enable required PHP extensions: PDO, pdo_mysql

4. **Initialize data**
   - Navigate to `variables.php` after logging in
   - Add initial values for weather, road conditions, traffic levels, and maneuvers

### Configuration

The `config.php` file contains two main sections:

- **Database Configuration**: MySQL connection parameters
- **Authentication**: Can be toggled on/off with `auth.enabled`

## Usage

### Getting Started

1. **Register an account**
   - Navigate to `/register.php`
   - Create an account with email and password

2. **Add driving experiences**
   - Click "Add experience" from the dashboard
   - Fill in the form with driving session details
   - Select multiple maneuvers if applicable

3. **View analytics**
   - Dashboard provides visual analytics of your driving data
   - Charts show distribution across weather, road, and traffic conditions

4. **Manage variables**
   - Add new categories (weather types, road conditions, etc.)
   - Activate/deactivate options as needed

### Authentication

Authentication can be disabled by setting `auth.enabled` to `false` in `config.php`. When disabled, the application runs in guest mode.

## Security Considerations

⚠️ **Important Security Notes:**

1. **Configuration File**: The `config.php` file contains database credentials. **Never commit real credentials to version control.**
   - Use environment variables in production
   - Add `config.php` to `.gitignore`
   - Create a `config.example.php` template with placeholder values

2. **Production Best Practices**: 
   - Store sensitive credentials in environment variables
   - Use a secrets management system
   - Implement proper access controls on configuration files
   - Regular security audits and dependency updates

3. **Security Features Implemented**:
   - Password hashing with bcrypt
   - Prepared SQL statements
   - HTML output escaping
   - Session-based authentication
   - CSRF protection recommended for production

## Development

This project was created as a homework assignment to demonstrate:
- PHP backend development
- MySQL database design with foreign keys
- User authentication and session management
- Data visualization with Chart.js
- CRUD operations
- Many-to-many relationships
- Responsive design

## License

This is an educational project created for academic purposes.

## Author

Built by a human under academic pressure 😊
