FROM php:8.4-apache

# تثبيت الحزم المطلوبة (بما فيها المكتبات اللي كانت ناقصة)
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# تثبيت إضافات PHP الضرورية
RUN docker-php-ext-install pdo_mysql mbstring gd zip

# تفعيل الـ Rewrite الخاص بالأباتشي
RUN a2enmod rewrite

# تعديل الـ Apache DocumentRoot ليشاور على مجلد public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# إعداد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع وتظبيط الصلاحيات
COPY . .
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
