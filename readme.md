# Sloph

Slog'd v2.0

```
sudo docker build -t rhiaro/sloph .

sudo docker run -d -p 80:80 -p 3306:3306 -v /home/rhiaro/Documents/sloph:/var/www/html -v /home/rhiaro/Documents/slogd/db:/var/lib/mysql/slogd --name sloph rhiaro/sloph
```

Sloph is the CMS behind rhiaro.co.uk.

## Data feeds

Data is stored as triples using (mostly) the [ActivityStreams 2.0](https://www.w3.org/TR/activitystreams-vocabulary) vocabulary. There are some extensions for things which aren't in AS2. Logs include:

* Articles and Notes
* Checkins
* Photo album updates and bookmarks
* Food and purchases

Data is accessible in various rdf syntaxes through content negotiation, as well as the HTML display in your browser.

Some data is available as paged collections.

There is a [SPARQL endpoint](https://rhiaro.co.uk/endpoint.php).

## Notifications

There is an [LDN](https://www.w3.org/TR/ldn) inbox to accept notifications.

## Posting clients

Provision for an [ActivityPub](https://www.w3.org/TR/activitypub) outbox is ongoing. When complete this means you can use any AP compliant clients to post data.