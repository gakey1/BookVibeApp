# BookVibe - Book Review Website

A modern, responsive book review platform built with PHP backend and Bootstrap frontend for Advanced Website Development.

## Features

###  Homepage
- Hero section with featured books
- Trending books with ratings and reviews
- Browse by genre categories
- Recent community reviews
- Newsletter signup

###  Book Management
- Browse 15 books with cover images
- Detailed book pages with reviews
- Star rating system (1-5 stars)
- Genre-based filtering and search
- Book favorites system

###  User System
- User registration and authentication
- User profiles and account management
- Personal favorites collection
- Review submission and management
- Session management with timeout

###  Modern UI/UX
- Fully responsive design (mobile-first)
- Professional purple theme (#6b21a7)
- Interactive JavaScript features
- Bootstrap 5 components
- Clean typography and spacing

## Technical Stack

- **Backend**: PHP 8+ with PDO
- **Database**: MySQL with optimized schema
- **Frontend**: Bootstrap 5, Custom CSS, JavaScript
- **Architecture**: MVC pattern with proper separation of concerns
- **Security**: Input sanitization, session management, CSRF protection
- **Design**: Professional purple theme, gradient-free styling

## Project Structure

```
BookVibeApp/
├── backend/
│   ├── login.php          # Redirects to frontend login
│   ├── register.php       # Redirects to frontend register
│   ├── login_handler.php  # Authentication business logic
│   ├── register_handler.php # Registration business logic
│   ├── logout.php         # Session termination
│   └── review_submit.php  # Review processing
├── config/
│   └── db.php            # Database configuration (XAMPP/MAMP)
├── database/
│   └── testdb.php        # Database testing utilities
└── frontend/
    ├── assets/
    │   ├── css/
    │   │   ├── style.css      # Main styling with purple theme
    │   │   ├── browse.css     # Browse page styles
    │   │   ├── login.css      # Login page styles
    │   │   ├── register.css   # Registration page styles
    │   │   ├── account.css    # Account page styles
    │   │   ├── favorites.css  # Favorites page styles
    │   │   ├── book_detail.css # Book detail page styles
    │   │   └── reviews.css    # Review page styles
    │   ├── js/
    │   │   ├── script.js      # Main interactive features
    │   │   ├── api.js         # API utility functions
    │   │   ├── register.js    # Registration page functionality
    │   │   ├── account.js     # Account page functionality
    │   │   ├── favorites.js   # Favorites page functionality
    │   │   ├── book_detail.js # Book detail page functionality
    │   │   └── reviews.js     # Review page functionality
    │   └── images/books/      # Book cover images (15 books)
    ├── includes/
    │   ├── header.php         # Navigation and head
    │   └── footer.php         # Footer and scripts
    ├── index.php             # Homepage
    ├── browse.php            # Book catalog
    ├── book_detail.php       # Individual book pages
    ├── login.php             # User login interface
    ├── register.php          # User registration interface
    ├── account.php           # User profile
    ├── favorites.php         # User favorites
    ├── reviews.php           # Review management
    └── admin_add_books.php   # Admin tool for adding books
```

## Setup Instructions

### Prerequisites
- XAMPP (Windows/Mac) or MAMP (Mac) or LAMP (Linux)
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Installation
1. **Clone the repository**
   ```bash
   git clone https://github.com/gakey1/BookVibeApp.git
   cd BookVibeApp
   ```

2. **Set up local server**
   - Start XAMPP/MAMP
   - Place project in `htdocs` (XAMPP) or `htdocs` (MAMP)

3. **Configure database**
   - Create MySQL database named `bookvibe`
   - Import `database/bookvibe.sql` (includes 15 books + sample data)
   - Database config auto-detects XAMPP/MAMP settings

4. **Access the application**
   - Navigate to `http://localhost/BookVibeApp/frontend/`
   - Register a new account or browse books

## Database Configuration

The application includes automatic environment detection:
- **XAMPP**: `localhost:3306`, user: `root`, password: (empty)
- **MAMP**: `localhost:8889`, user: `root`, password: `root`
- **Custom**: Modify `config/db.php` for other setups

### Database Import Instructions
1. **Start your local server** (XAMPP/MAMP)
2. **Access database admin**:
   - XAMPP: `http://localhost/phpmyadmin`
   - MAMP: `http://localhost:8889/phpMyAdmin`
3. **Create new database** named `bookvibe`
4. **Import SQL file**: Select and import `database/bookvibe.sql`
5. **Verify import**: Database should contain 15 books across 10 genres

**Note**: The database file includes all sample data - no additional scripts needed.

## Team Collaboration

### Development Timeline
- **Oct 13-24**: Backend foundation and core functionality (Tracy)
- **Oct 31**: Frontend interface and user experience (Gakey1)
- **Nov 8**: Architecture improvements and code organization

### Contributors
- **Backend Development**: Tracy Nguyen
  - PHP authentication system
  - Database architecture
  - Core functionality implementation
  
- **Frontend Development**: Gakey1
  - Modern responsive UI/UX
  - JavaScript interactivity
  - Professional purple theme design
  - User interface optimization
  - MVC architecture implementation

## Features Implemented

- **User Authentication** - Registration, login, logout with MVC architecture
- **Book Catalog** - 15 books with covers, ratings, and details  
- **Review System** - Star ratings and written reviews with modal interfaces
- **Responsive Design** - Mobile-first, professional purple theme
- **Search & Filter** - Genre-based browsing with real database genres
- **User Favorites** - Personal book collections with enhanced filtering
- **Database Integration** - XAMPP/MAMP compatibility with proper session management
- **Security Features** - Input validation, session management, and MVC separation
- **Code Quality** - Proper separation of concerns, externalized CSS/JS
- **Admin Tools** - Database management scripts for adding new books

## Recent Improvements (November 2025)

### Architecture Enhancements
- **MVC Implementation** - Proper separation of concerns for authentication system
- **Session Management** - Fixed duplicate session_start() conflicts
- **Code Organization** - Externalized CSS/JS from PHP files for better maintainability

### UI/UX Improvements  
- **Theme Standardization** - Unified purple color scheme (#6b21a7) across all pages
- **Design Cleanup** - Removed gradients and automated-looking elements
- **Typography** - Improved font hierarchy and spacing consistency
- **Modal System** - Enhanced review and delete confirmation modals

### Bug Fixes
- **JavaScript Conflicts** - Resolved star rating display issues in reviews
- **Database Consistency** - Fixed column name mismatches throughout application
- **Responsive Design** - Fixed layout issues and improved mobile experience
- **Authentication Flow** - Streamlined login/registration process with proper error handling

### New Content
- **Book Expansion** - Added 3 additional books (Run, Red City, All That We See Or Seem) to database
- **Genre Updates** - Updated filters to match actual database genres including Adventure and Sci-Fi
- **Database Synchronization** - All books now included in standard SQL import

## Next Steps & Future Enhancements

### Phase 4: Google Books API Integration
**Status**: Planned for implementation after current features are stable

####  **Real Book Data Integration**
- **Google Books API Client**: Search and import real book data from Google Books
- **Dynamic Cover System**: Support for external cover URLs with local fallbacks
- **ISBN Lookup**: Get book details by ISBN for accurate data
- **Genre Mapping**: Map Google Books categories to local genre system

####  **Technical Implementation**
```
📁 New Files to Add:
├── includes/
│   ├── book_api.php         # Google Books API client
│   └── book_diversity.php   # Advanced book selection algorithm
├── api/
│   └── books.php           # RESTful API endpoints
└── admin/
    └── import_books.php    # Book import interface
```

####  **Database Enhancements**
```sql
-- Add Google Books integration fields
ALTER TABLE books ADD COLUMN google_books_id VARCHAR(50);
ALTER TABLE books ADD COLUMN display_weight DECIMAL(3,2) DEFAULT 1.00;
ALTER TABLE books ADD COLUMN last_displayed TIMESTAMP NULL;
```

####  **Advanced Features Planned**
- **Real-time Search**: Search Google Books API for new books
- **Bulk Import**: Import book data with covers directly to database
- **Book Diversity System**: Prevent duplicate displays across sections
- **Dynamic Cover Processing**: Handle external URLs + local file fallbacks
- **Performance Optimization**: API caching and lazy loading

####  **API Endpoints to Implement**
```
GET /api/books.php?action=search&q=query    # Search Google Books
GET /api/books.php?action=import&isbn=X     # Import by ISBN
GET /api/books.php?action=trending&genre=X  # Get trending books
GET /api/books.php?action=covers&book_id=X  # Get cover URLs
```

### Phase 5: Advanced User Features
- **Reading Lists**: Create custom book lists and collections
- **Social Features**: Follow other users and see their reviews
- **Recommendation Engine**: Advanced book recommendations
- **Review Analytics**: Advanced review insights and trends
- **Mobile App**: Progressive Web App (PWA) functionality

### Phase 6: Performance & Scalability
- **Caching System**: Redis/Memcached for API and database caching
- **CDN Integration**: Image and asset delivery optimization
- **Search Engine**: Full-text search with Elasticsearch
- **Analytics Dashboard**: User engagement and book popularity metrics

### Phase 7: Production Deployment
- **Security Hardening**: HTTPS, CSRF protection, input validation
- **Database Optimization**: Indexing, query optimization, connection pooling
- **Monitoring**: Error tracking, performance monitoring, uptime alerts
- **Backup System**: Automated database and file backups


## Academic Project

This is a collaborative academic project for Advanced Web unit, demonstrating modern web development practices, team collaboration, and full-stack development skills.
