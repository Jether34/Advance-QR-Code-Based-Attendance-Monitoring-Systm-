# Contributing to Smart Attendance System

Thank you for your interest in contributing to our Smart Attendance System! This document provides guidelines and workflows for team collaboration.

## 🚀 Getting Started

### Prerequisites
- Git installed on your local machine
- XAMPP/WAMP/LAMP stack for local development
- VS Code with recommended extensions
- GitHub account with repository access

### Initial Setup
1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/smart-attendance-system.git
   cd smart-attendance-system
   ```

2. **Set up environment**:
   ```bash
   copy .env.example .env
   # Edit .env with your local database credentials
   ```

3. **Run database setup**:
   ```bash
   .\setup_project.ps1
   ```

## 🌳 Branch Strategy

### Main Branches
- **`main`**: Production-ready code, always stable
- **`develop`**: Integration branch for ongoing development
- **`staging`**: Pre-production testing branch

### Feature Branches
- **`feature/feature-name`**: New features or enhancements
- **`bugfix/bug-description`**: Bug fixes for develop branch
- **`hotfix/critical-fix`**: Urgent fixes for production

### Branch Naming Conventions
```
feature/add-analytics-dashboard
feature/implement-parent-notifications
bugfix/fix-qr-scanner-mobile-issue
hotfix/critical-database-security-patch
```

## 🔄 Development Workflow

### 1. Starting New Work
```bash
# Switch to develop branch
git checkout develop
git pull origin develop

# Create feature branch
git checkout -b feature/your-feature-name
```

### 2. Development Process
- Write clean, documented code
- Follow PHP coding standards (PSR-12)
- Test locally before committing
- Write meaningful commit messages

### 3. Committing Changes
```bash
# Stage changes
git add .

# Commit with descriptive message
git commit -m "feat: add real-time attendance notifications

- Implement WebSocket connection for live updates
- Add notification system for teachers
- Update UI with real-time status indicators
- Add tests for notification functionality"
```

### 4. Pushing and Pull Requests
```bash
# Push feature branch
git push origin feature/your-feature-name

# Create pull request on GitHub
# Request review from team members
```

## 📝 Commit Message Guidelines

### Format
```
<type>(<scope>): <description>

<body>

<footer>
```

### Types
- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style changes (formatting, etc.)
- **refactor**: Code refactoring
- **test**: Adding or updating tests
- **chore**: Maintenance tasks

### Examples
```bash
feat(qr): add embedded student information in QR codes
fix(scanner): resolve mobile camera access issues
docs(readme): update setup instructions for team collaboration
refactor(auth): improve session management security
```

## 🧪 Testing Requirements

### Before Committing
- [ ] Test all new functionality locally
- [ ] Verify existing features still work
- [ ] Check mobile compatibility
- [ ] Validate database operations
- [ ] Review code for security issues

### Testing Checklist
```bash
# Test QR code generation
# Test scanner functionality on mobile
# Verify attendance recording accuracy
# Check role-based access controls
# Test database connection and queries
```

## 💻 Code Standards

### PHP Guidelines
- Follow PSR-12 coding standard
- Use meaningful variable and function names
- Add PHPDoc comments for functions and classes
- Validate and sanitize all user inputs
- Use prepared statements for database queries

### JavaScript Guidelines
- Use ES6+ features consistently
- Follow camelCase naming convention
- Add comments for complex logic
- Handle errors gracefully
- Use async/await for asynchronous operations

### CSS Guidelines
- Use BEM methodology for class naming
- Maintain responsive design principles
- Organize styles logically
- Use CSS custom properties for theming
- Ensure cross-browser compatibility

## 🔒 Security Guidelines

### Database Security
- Always use PDO prepared statements
- Validate input on both client and server
- Implement proper session management
- Use HTTPS in production
- Regular security audits

### Code Review Checklist
- [ ] No SQL injection vulnerabilities
- [ ] Proper input validation and sanitization
- [ ] Secure session handling
- [ ] No sensitive data in logs
- [ ] CSRF protection where needed

## 📱 Mobile Development

### Responsive Design
- Test on multiple screen sizes
- Ensure touch-friendly interfaces
- Optimize for mobile performance
- Consider offline functionality
- Test camera access permissions

### QR Scanner Optimization
- Validate camera permissions
- Handle scanning errors gracefully
- Provide clear user feedback
- Test with various QR code formats
- Ensure fast scan performance

## 🗄️ Database Guidelines

### Migration Process
- Create migration scripts for schema changes
- Include rollback procedures
- Test migrations on sample data
- Document all changes
- Coordinate with team on breaking changes

### Data Management
- Use transactions for related operations
- Implement proper indexing
- Regular backup procedures
- Monitor query performance
- Maintain data integrity constraints

## 📊 Performance Guidelines

### Frontend Optimization
- Minimize HTTP requests
- Optimize images and assets
- Use efficient JavaScript patterns
- Implement caching strategies
- Monitor Core Web Vitals

### Backend Optimization
- Optimize database queries
- Implement appropriate caching
- Monitor server performance
- Use efficient algorithms
- Profile critical code paths

## 🚀 Deployment Process

### Development to Staging
1. Merge feature branch to develop
2. Deploy to staging environment
3. Run automated tests
4. Manual testing and QA
5. Performance testing

### Staging to Production
1. Merge develop to main
2. Create release tag
3. Deploy to production
4. Monitor system health
5. Update documentation

## 📞 Communication

### Code Reviews
- Be constructive and respectful
- Explain reasoning for changes
- Ask questions for clarification
- Suggest improvements
- Approve when ready

### Issue Reporting
- Use GitHub Issues for bug reports
- Provide detailed reproduction steps
- Include screenshots for UI issues
- Label issues appropriately
- Reference related pull requests

### Team Coordination
- Daily standups for progress updates
- Weekly sprint planning
- Monthly retrospectives
- Slack/Discord for quick communication
- GitHub Discussions for technical topics

## 🏆 Recognition

### Code Quality Awards
- Clean code practices
- Innovative solutions
- Performance improvements
- Security enhancements
- Team collaboration

### Contribution Levels
- **Contributor**: Regular commits and participation
- **Maintainer**: Code review and merge permissions
- **Admin**: Full repository access and project oversight

## 📚 Resources

### Documentation
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Reference](https://dev.mysql.com/doc/)
- [JavaScript MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [Git Best Practices](https://git-scm.com/book/en/v2)

### Tools and Extensions
- VS Code Live Share for pair programming
- GitHub Desktop for Git GUI
- XAMPP for local development
- Postman for API testing

---

**Happy coding! 🎉**

Together we build better software through collaboration, quality, and continuous improvement.