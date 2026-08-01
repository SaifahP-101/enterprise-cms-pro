# docker/php/Dockerfile
FROM php:8.0-fpm

# ติดตั้ง System Dependencies และ PHP Extensions ที่จำเป็นสำหรับ Laravel & Image Processing
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev \
    libxml2-dev

# เคลียร์ Cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# ติดตั้ง PHP Extensions แกนหลัก
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# ดึง Composer มาใส่ใน Container
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# ตั้งค่า Directory เริ่มต้น
WORKDIR /var/www

# ปรับ Permission ให้รองรับการอัปโหลดไฟล์
RUN chown -R www-data:www-data /var/www