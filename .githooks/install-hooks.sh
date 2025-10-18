#!/bin/bash

# Siroko Cart - Install Git hooks script
# This script installs the custom Git hooks

echo "🔧 Installing Git hooks..."

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Create .git/hooks directory if it doesn't exist
mkdir -p "$PROJECT_ROOT/.git/hooks"

# Install pre-commit hook
if [ -f "$SCRIPT_DIR/pre-commit" ]; then
    cp "$SCRIPT_DIR/pre-commit" "$PROJECT_ROOT/.git/hooks/pre-commit"
    chmod +x "$PROJECT_ROOT/.git/hooks/pre-commit"
    echo "✅ Pre-commit hook installed"
else
    echo "❌ Pre-commit hook not found"
    exit 1
fi

# Install commit-msg hook if it exists
if [ -f "$SCRIPT_DIR/commit-msg" ]; then
    cp "$SCRIPT_DIR/commit-msg" "$PROJECT_ROOT/.git/hooks/commit-msg"
    chmod +x "$PROJECT_ROOT/.git/hooks/commit-msg"
    echo "✅ Commit-msg hook installed"
fi

echo ""
echo "🎉 Git hooks installed successfully!"
echo ""
echo "The following hooks are now active:"
echo "  • pre-commit: Runs code quality checks before each commit"
if [ -f "$SCRIPT_DIR/commit-msg" ]; then
    echo "  • commit-msg: Validates commit message format"
fi
echo ""
echo "To disable hooks temporarily, use: git commit --no-verify"
echo ""