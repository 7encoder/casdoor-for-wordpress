# Contributing to Casdoor WordPress Plugin

Thank you for your interest in contributing! This document provides guidelines for contributing to the project.

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Create a new branch for your feature or bugfix
4. Make your changes
5. Test your changes thoroughly
6. Submit a pull request

## Development Setup

### Requirements

- PHP 7.4 or higher (8.3 recommended for testing)
- WordPress 5.8 or higher
- Composer
- Git
- A local WordPress development environment (e.g., Local, MAMP, Docker)

### Installation for Development

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/casdoor-for-wordpress.git
cd casdoor-for-wordpress

# Install dependencies
composer install

# Link to WordPress plugins directory
ln -s $(pwd) /path/to/wordpress/wp-content/plugins/casdoor-wordpress-plugin
```

## Coding Standards

### PHP

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

- Use tabs for indentation
- Maximum line length of 100 characters (soft limit)
- Proper spacing around operators
- Braces on their own lines for functions and classes
- Single quotes for strings (unless interpolating)

Example:
```php
<?php
function casdoor_example_function( $param ) {
    if ( ! empty( $param ) ) {
        return sanitize_text_field( $param );
    }
    return '';
}
```

### JavaScript

- Use modern ES6+ syntax where appropriate
- Use semicolons
- Single quotes for strings
- Proper indentation (4 spaces)

### Documentation

- Add PHPDoc blocks for all functions and classes
- Include `@param`, `@return`, and `@since` tags
- Document security considerations
- Add inline comments for complex logic

Example:
```php
/**
 * Get Casdoor login URL with optional redirect
 *
 * @since 1.0.0
 *
 * @param string $redirect Optional. URL to redirect after login.
 * @return string Login URL
 */
function casdoor_get_login_url( $redirect = '' ) {
    // Implementation
}
```

## Security Guidelines

### Always

- ✅ Sanitize all user input
- ✅ Escape all output
- ✅ Validate data types
- ✅ Use nonces for forms
- ✅ Check user capabilities
- ✅ Use prepared statements for database queries
- ✅ Validate and sanitize redirect URLs
- ✅ Use HTTPS in production

### Never

- ❌ Trust user input
- ❌ Use `eval()` or similar functions
- ❌ Store passwords in plain text
- ❌ Expose sensitive data in logs
- ❌ Disable SSL verification in production
- ❌ Use deprecated WordPress functions

## Testing

### Manual Testing

1. Test on multiple PHP versions (7.4, 8.0, 8.1, 8.2, 8.3)
2. Test on multiple WordPress versions
3. Test with WooCommerce enabled and disabled
4. Test all configuration options
5. Test error scenarios
6. Test with various themes

### Security Testing

Before submitting:
1. Run syntax check: `php -l file.php`
2. Review for common vulnerabilities (XSS, SQL injection, CSRF)
3. Verify input sanitization
4. Verify output escaping
5. Test redirect validation

## Pull Request Process

### Before Submitting

- [ ] Code follows WordPress coding standards
- [ ] All PHP files pass syntax check
- [ ] Security best practices followed
- [ ] Documentation updated if needed
- [ ] CHANGELOG.md updated
- [ ] No debugging code left in
- [ ] Tested manually

### PR Description

Include:
1. Description of changes
2. Motivation and context
3. Type of change (bug fix, feature, docs, etc.)
4. Testing performed
5. Screenshots (if applicable)
6. Related issues

### PR Title Format

Use conventional commits format:
- `feat: Add new feature`
- `fix: Fix bug description`
- `docs: Update documentation`
- `refactor: Refactor code`
- `test: Add or update tests`
- `chore: Update dependencies`
- `security: Security fix`

Example:
```
feat: Add support for custom redirect after logout
```

## Commit Guidelines

### Commit Messages

Follow conventional commits:
```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Formatting, missing semicolons, etc.
- `refactor`: Code change that neither fixes a bug nor adds a feature
- `test`: Adding tests
- `chore`: Updating build tasks, package manager configs, etc.

Example:
```
feat(auth): Add automatic token refresh

Implement automatic access token refresh before expiration
to prevent logout during active sessions.

Closes #123
```

## Code Review Process

1. **Automated Checks**: GitHub Actions will run automatic checks
2. **Maintainer Review**: A maintainer will review your PR
3. **Address Feedback**: Make requested changes
4. **Approval**: Once approved, your PR will be merged
5. **Release**: Changes included in next release

## Feature Requests

For new features:
1. Open an issue first to discuss
2. Wait for maintainer feedback
3. If approved, create PR with implementation

## Bug Reports

When reporting bugs, include:
- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages or logs
- Screenshots if applicable

## Questions

- Open a GitHub Discussion for questions
- Check existing issues and documentation first
- Be respectful and patient

## License

By contributing, you agree that your contributions will be licensed under the Apache License 2.0.

## Recognition

Contributors will be:
- Listed in release notes
- Credited in CHANGELOG.md
- Mentioned in security advisories (if applicable)

## Code of Conduct

Be respectful, inclusive, and professional. We follow the [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/).

## Contact

- GitHub Issues: https://github.com/7encoder/casdoor-for-wordpress/issues
- Discussions: https://github.com/7encoder/casdoor-for-wordpress/discussions

Thank you for contributing to Casdoor WordPress Plugin! 🎉
