RENAME .git .main_git
git init
git remote add origin https://github.com/Noctissinnit/booking.git
git add .
git commit -m "Initial Commit"
git push origin main
@RD /S /Q .git
RENAME .main_git .git