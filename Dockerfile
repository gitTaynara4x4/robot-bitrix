# Use uma imagem base PHP com Apache
FROM php:8.1-apache

# Instale extensões PHP necessárias (se precisar de outras, adicione aqui)
RUN docker-php-ext-install pdo pdo_mysql

# Copie os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html/

# Defina permissões
RUN chown -R www-data:www-data /var/www/html/

# Exponha a porta 80
EXPOSE 435

# Inicialize o servidor Apache
CMD ["apache2-foreground"]
