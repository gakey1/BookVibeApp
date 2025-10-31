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
- Browse 12+ books with cover images
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
- Modern BookVibe design system
- Interactive JavaScript features
- Bootstrap 5 components
- Professional typography and spacing

## Technical Stack

- **Backend**: PHP 8+ with PDO
- **Database**: MySQL with optimized schema
- **Frontend**: Bootstrap 5, Custom CSS, JavaScript
- **Architecture**: MVC pattern with Database singleton
- **Security**: Input sanitization, session management, CSRF protection

## Project Structure

```
BookVibeApp/
├── backend/
│   ├── login.php          # User authentication
│   ├── register.php       # User registration
│   ├── logout.php         # Session termination
│   └── review_submit.php  # Review processing
├── config/
│   └── db.php            # Database configuration (XAMPP/MAMP)
├── database/
│   └── testdb.php        # Database testing utilities
└── frontend/
    ├── assets/
    │   ├── css/style.css      # Custom styling
    │   ├── js/script.js       # Interactive features
    │   └── images/books/      # Book cover images (12 books)
    ├── includes/
    │   ├── header.php         # Navigation and head
    │   └── footer.php         # Footer and scripts
    ├── index.php             # Homepage
    ├── browse.php            # Book catalog
    ├── book_detail.php       # Individual book pages
    ├── account.php           # User profile
    ├── favorites.php         # User favorites
    └── reviews.php           # Review management
```

## Setup Instructions

### Prerequisites
- XAMPP (Windows/Mac) or MAMP (Mac) or LAMP (Linux)
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Installation
1. **Clone the repository**
   ```bash
   git clone https://github.com/username/book-review-website.git
   cd book-review-website
   ```

2. **Set up local server**
   - Start XAMPP/MAMP
   - Place project in `htdocs` (XAMPP) or `htdocs` (MAMP)

3. **Configure database**
   - Create MySQL database named `bookvibe`
   - Import database schema (if available)
   - Database config auto-detects XAMPP/MAMP settings

4. **Access the application**
   - Navigate to `http://localhost/book-review-website/frontend/`
   - Register a new account or browse books

## Database Configuration

The application includes intelligent environment detection:
- **XAMPP**: `localhost:3306`, user: `root`, password: (empty)
- **MAMP**: `localhost:8889`, user: `root`, password: `root`
- **Custom**: Modify `config/db.php` for other setups

## Team Collaboration

### Development Timeline
- **Oct 13-24**: Backend foundation and core functionality (Tracy)
- **Oct 31**: Frontend interface and user experience (Gakey1)
- **Nov 1**: Bug fixes and final polish

### Contributors
- **Backend Development**: Tracy Nguyen
  - PHP authentication system
  - Database architecture
  - Core functionality implementation
  
- **Frontend Development**: Gakey1
  - Modern responsive UI/UX
  - JavaScript interactivity
  - BookVibe design system
  - User interface optimization

## Features Implemented

- **User Authentication** - Registration, login, logout with sessions  
- **Book Catalog** - 12 books with covers, ratings, and details  
- **Review System** - Star ratings and written reviews  
- **Responsive Design** - Mobile-first, modern UI  
- **Search & Filter** - Genre-based browsing  
- **User Favorites** - Personal book collections  
- **Database Integration** - XAMPP/MAMP compatibility  
- **Security Features** - Input validation and session management

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

####  **Smart Features Planned**
- **Real-time Search**: Search Google Books API for new books
- **Automatic Import**: Import book data with covers directly to database
- **Book Diversity System**: Prevent duplicate displays across sections
- **Intelligent Cover Processing**: Handle external URLs + local file fallbacks
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
- **Recommendation Engine**: AI-powered book recommendations
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
