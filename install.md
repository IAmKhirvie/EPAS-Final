# Clone the repository

git clone https://github.com/IAmKhirvie/EPAS-E.git
cd EPAS-E

# Install dependencies

composer install
npm install

# Environment setup

copy .env.example .env
echo (Make sure that .env is set to the password)
php artisan key:generate
php artisan storage:link

# Database setup

php artisan migrate
php artisan db:seed

# Start development servers

php artisan serve
npm run dev
