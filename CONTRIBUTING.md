# Contributing to Log Sentinel v2.0

First off, thank you for considering contributing to Log Sentinel! 🛡️

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)

---

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code:

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

---

## How Can I Contribute?

### 🐛 Reporting Bugs

Before creating bug reports, please check existing issues. When creating a bug report, include:

- **Clear title** describing the issue
- **Steps to reproduce** the behavior
- **Expected behavior** vs actual behavior
- **Environment details** (OS, Python version, Laravel version)
- **Screenshots** if applicable

### 💡 Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. Include:

- **Use case** - Why is this feature needed?
- **Proposed solution** - How should it work?
- **Alternatives considered** - What other approaches did you consider?

### 🔧 Code Contributions

Areas where contributions are welcome:

| Area | Description |
|------|-------------|
| **ML Models** | New ensemble algorithms, improved feature engineering |
| **XAI** | Alternative explainability methods (LIME, Anchors) |
| **Visualization** | New chart types, improved dashboards |
| **Testing** | Additional unit tests, integration tests |
| **Documentation** | API docs, tutorials, translations |

---

## Development Setup

### Prerequisites

- Python 3.10+
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Local Setup

```bash
# Clone repository
git clone https://github.com/el-pablos/uas-kecerdasan-artifisial-kel3.git
cd uas-kecerdasan-artifisial-kel3

# Setup Laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Setup Python ML Service
cd ml_service
python -m venv venv
.\venv\Scripts\activate  # Windows
source venv/bin/activate # Linux/Mac
pip install -r requirements.txt

# Run tests
pytest -v tests/
```

---

## Pull Request Process

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes with descriptive messages
4. **Test** your changes thoroughly
5. **Push** to your fork (`git push origin feature/amazing-feature`)
6. **Open** a Pull Request

### Commit Message Format

Use Indonesian casual style for commit messages (project convention):

```
feat: nambahin fitur baru buat deteksi ddos
fix: benerin bug di ensemble voting
docs: update readme dengan contoh baru
test: tambahin unit test buat shap explainer
style: rapiin format code di app.py
```

---

## Coding Standards

### Python (ML Service)

- Follow PEP 8 style guide
- Use type hints where possible
- Document functions with docstrings
- Keep functions under 50 lines

### PHP (Laravel)

- Follow PSR-12 coding standard
- Use Laravel conventions for naming
- Keep controllers thin, use services

### JavaScript

- Use ES6+ features
- Document complex functions
- Keep functions pure when possible

---

## 🙏 Recognition

Contributors will be added to the README.md and CITATION.cff files.

---

**Lead Maintainer:** Muhammad Akbar Hadi Pratama ([@el-pablos](https://github.com/el-pablos))
