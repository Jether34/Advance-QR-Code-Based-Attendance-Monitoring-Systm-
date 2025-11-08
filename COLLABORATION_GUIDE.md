# Smart Attendance System - Team Collaboration Quick Reference

## For Project Owner (You):
```bash
# 1. Commit collaboration files
git add .
git commit -m "feat: add complete team collaboration setup"

# 2. Update remote with your GitHub username
git remote set-url origin https://github.com/YOUR_GITHUB_USERNAME/smart-attendance-system.git

# 3. Push to GitHub
git push -u origin master
git push -u origin develop

# 4. Add collaborators on GitHub.com → Settings → Collaborators
```

## For Team Members:
```bash
# 1. Clone repository
git clone https://github.com/OWNER_USERNAME/smart-attendance-system.git
cd smart-attendance-system

# 2. Automated setup
.\team_setup.bat

# 3. Start developing
git checkout develop
git checkout -b feature/your-feature-name
```

## VS Code Live Share:
```
1. Install "Live Share" extension
2. Click "Live Share" in status bar
3. Share invitation link with team
4. Code together in real-time!
```

## Development Workflow:
```bash
# Daily workflow
git checkout develop
git pull origin develop
git checkout -b feature/new-feature
# ... make changes with team via Live Share ...
git add .
git commit -m "feat: description"
git push origin feature/new-feature
# Create pull request on GitHub
```

## Mobile Testing:
- **Host Local**: http://localhost/smart-attendance-system  
- **Network Access**: http://YOUR_IP/smart-attendance-system
- **QR Scanning**: Use network IP for mobile devices
- **Live Share**: Team members can access your localhost

## Key Benefits:
✅ Real-time collaborative coding
✅ Shared localhost for testing  
✅ Automatic environment setup
✅ Professional Git workflow
✅ Mobile QR testing together
✅ Code review process
✅ Integrated communication