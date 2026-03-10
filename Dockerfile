FROM php:8.2-apache

# Habilita módulo de reescrita de URL do Apache
RUN a2enmod rewrite

# Instala extensão mysqli (necessária para o config.php)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copia todo o projeto para dentro do servidor
COPY . /var/www/html/

# Permissões corretas para o Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
