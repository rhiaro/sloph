# Sloph

Slog'd v2.0

```
sudo docker build -t rhiaro/sloph .

sudo docker run -d -p 80:80 -p 3306:3306 -v /home/rhiaro/Documents/sloph:/var/www/html -v /home/rhiaro/Documents/slogd/db:/var/lib/mysql/slogd --name sloph rhiaro/sloph
```