#!/bin/sh

echo "🔧 Installing Git hooks..."

# Configure Git to use .githooks directory
git config core.hooksPath .githooks

echo "✅ Git hooks installed successfully!"
echo "Hooks configured:"
echo "  - pre-commit: PHPStan + PHP CS Fixer"
echo "  - commit-msg: Conventional Commits validation"
