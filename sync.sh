#!/bin/bash

# Xepanel Auto-Sync Script
echo "🚀 Starting Sync to GitHub..."

# Add all changes
git add .

# Ask for commit message or use default
read -p "Commit message (default: 'Update Xepanel'): " msg
if [ -z "$msg" ]; then
    msg="Update Xepanel"
fi

# Commit
git commit -m "$msg"

# Push
echo "📤 Pushing to GitHub..."
git push origin main

echo "✅ Sync complete!"
