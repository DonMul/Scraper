FROM ubuntu
MAINTAINER scraper@jmul.net

RUN apt-get update && apt-get install -y tor php7.0 git php7.0-mysql php7.0-curl php-xml
RUN mkdir /root/scraper && git clone https://github.com/DonMul/Scraper.git /root/scraper
ADD Settings.php /root/scraper/