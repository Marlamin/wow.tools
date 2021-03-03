FROM php:7.4-fpm AS base
RUN docker-php-ext-install mysqli pdo pdo_mysql