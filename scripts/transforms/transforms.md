[x] Move properties from raw to rendered

```
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

insert into <http://blog.rhiaro.co.uk#> {
 ?fancy ?p ?o .
 ?s ?p2 ?fancy .
} where {
 ?raw foaf:isPrimaryTopicOf ?fancy .
 ?raw ?p ?o .
 ?s ?p2 ?raw .
}
```

```
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
delete {
    ?s foaf:isPrimaryTopicOf ?s .
    ?s foaf:primaryTopic ?s .
    ?s foaf:primaryTopic ?o .
} WHERE {
    ?s foaf:isPrimaryTopicOf ?s .
    ?s foaf:primaryTopic ?s .
    ?s foaf:primaryTopic ?o .
}
```

```
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
delete {
    ?s ?p ?o .
} where {
    ?s foaf:isPrimaryTopicOf ?fancy .
    ?s ?p ?o .
}

```

[x] dc:creator to as:actor

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix dct: <http://purl.org/dc/elements/1.1/> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:actor ?o .
} where {
    ?s dct:creator ?o . 
}
```

```
@prefix dct: <http://purl.org/dc/elements/1.1/> .
delete {
    ?s dct:creator ?o .
}
```

[x] dc:created to as:published

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix dc: <http://purl.org/dc/terms/> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:published ?o .
} where {
    ?s dc:created ?o . 
}
```

```
@prefix dc: <http://purl.org/dc/terms/> .
delete {
    ?s dc:created ?o .
}
```

[x] dc:modified to as:updated

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix dc: <http://purl.org/dc/terms/> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:updated ?o .
} where {
    ?s dc:modified ?o . 
}
```

```
@prefix dc: <http://purl.org/dc/terms/> .
delete {
    ?s dc:modified ?o .
}
```

[x] dc:title to as:name

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix dc: <http://purl.org/dc/terms/> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:name ?o .
} where {
    ?s dc:title ?o . 
}
```

```
@prefix dc: <http://purl.org/dc/terms/> .
delete {
    ?s dc:title ?o .
}
```

[x] sioc:content to as:content

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix sioc: <http://rdfs.org/sioc/types#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:content ?content .
} where {
    ?s sioc:content ?content . 
}
```

```
@prefix sioc: <http://rdfs.org/sioc/types#> .
delete {
    ?s sioc:content ?o .
}
```

[x] sioc:topic to as:tag

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix sioc: <http://rdfs.org/sioc/types#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:tag ?o .
} where {
    ?s sioc:topic ?o . 
}
```

```
@prefix sioc: <http://rdfs.org/sioc/types#> .
delete {
    ?s sioc:topic ?o .
}
```

[x] blog:like_of to as:object and a Like

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .
insert into <http://blog.rhiaro.co.uk#> {
    ?post a as:Like .
    ?post as:object ?object .
} where {
    ?post blog:like_of ?object .   
}
```

```
@prefix blog: <http://vocab.amy.so/blog#> .
delete {
    ?post blog:like_of ?object .
}
```

[x] blog:share_of to as:object and a Announce

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .
insert into <http://blog.rhiaro.co.uk#> {
    ?post a as:Announce .
    ?post as:object ?object .
} where {
    ?post blog:share_of ?object .   
}
```

```
@prefix blog: <http://vocab.amy.so/blog#> .
delete {
    ?post blog:share_of ?object .
}
```

[x] blog:bookmark_of to as:object and a Add with as:target <??>

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
insert into <http://blog.rhiaro.co.uk#> {
    <https://rhiaro.co.uk/bookmarks/> a as:Collection ;
                                      as:name "Bookmarks" .
}
```
 
```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .
insert into <http://blog.rhiaro.co.uk#> {
    ?post a as:Add .
    ?post as:object ?object .
    ?post as:target <https://rhiaro.co.uk/bookmarks/> .
} where {
    ?post blog:bookmark_of ?object .   
}
```

```
@prefix blog: <http://vocab.amy.so/blog#> .
delete {
    ?post blog:bookmark_of ?object .
}
```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
insert into <http://blog.rhiaro.co.uk#> {
    <https://rhiaro.co.uk/bookmarks/> as:items ?bm .
} where {
    ?post a as:Add .
    ?post as:object ?bm .
    ?post as:target <https://rhiaro.co.uk/bookmarks/> .
}
```

Note.. to display bookmarks in all their glory...

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
select ?url ?name ?content ?date ?tag where {
    <https://rhiaro.co.uk/bookmarks/> as:items ?url .
    ?add a as:Add .
    ?add as:target <https://rhiaro.co.uk/bookmarks/> .
    ?add as:object ?url .
    optional { ?add as:name ?name . }
    optional { ?add as:content ?content . }
    optional { ?add as:published ?date . }
    optional { ?add as:tag ?tag .  }
}
```

[x] Fix Accepts and Events
    -> TODO: update seeulator to send an accept and an event or something.. and I think Accept inReplyTo is fine in the case without Invite
    -> This got a bit out of hand, give up and use `events.sparql`

> Checkin: 20160729-1946

[x] Add a as:Activity to blog:Consumption and blog:Acquisition

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s a as:Activity .
} where {
    { ?s a blog:Consumption . } UNION { ?s a blog:Acquisition . }
}
```

[x] Add a as:Arrive to checkins

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
insert into <http://blog.rhiaro.co.uk#> {
    ?s a as:Arrive .
} where {
    { ?s as:location <http://rhiaro.co.uk/location/transit> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/other> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/home> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/meeting> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/seminar> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/office> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/volunteer> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/food> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/exercise> }
    UNION
    { ?s as:location <http://rhiaro.co.uk/location/event> }
}
```

[x] Add a as:Place to /location/s

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
insert into <http://blog.rhiaro.co.uk#> {
    ?loc a as:Place .
} where {
    ?s a as:Arrive .
    ?s as:location ?loc .
}
```

> Checkin: 20160730-1112

[x] Make everything https 

`sed -i 's/http:\/\/rhiaro.co.uk/https:\/\/rhiaro.co.uk/g' *.ttl`

> Checkin: 20160801-1019

[x] sioc:reply_of to as:inReplyTo

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix sioc: <http://rdfs.org/sioc/types#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s as:inReplyTo ?content .
} where {
    ?s sioc:reply_of ?content . 
}
```

```
@prefix sioc: <http://rdfs.org/sioc/types#> .
delete {
    ?s sioc:reply_of ?o .
}
```

> Checkin: 20160801-1347

[x] foaf:name to as:name

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

insert into <http://blog.rhiaro.co.uk#> {
 ?s as:name ?o .
} where {
 ?s foaf:name ?o .
}
```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

delete {
    ?s foaf:name ?o .
}
```

[x] foaf:depiction to as:image

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

insert into <http://blog.rhiaro.co.uk#> {
 ?s as:image ?o .
} where {
 ?s foaf:depiction ?o .
}
```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

delete {
    ?s foaf:depiction ?o .
}
```

[x] foaf:homepage to as:url

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

insert into <http://blog.rhiaro.co.uk#> {
 ?s as:url ?o .
} where {
 ?s foaf:homepage ?o .
}
```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

delete {
    ?s foaf:homepage ?o .
}
```

> Checkin: 20160801-1506


[x] blog:mentions -> Relationship

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .
insert into <http://blog.rhiaro.co.uk#> {
    ? a as:Relationship .
    ? as:subject ?post .
    ? as:object ?object .
    ? as:relationship as:href .
    ? as:published ?pub .
} where {
    ?post blog:mentions ?object .
    ?post as:published ?pub .   
}
```

```
scripts/transforms.php transform_mentions();
```


[x] Convert Llogposts

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?post a blog:Sleep .
}where{
    ?post a blog:LlogPost .
    ?post as:tag "sleep" .
}

```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?post a blog:Consumption .
    ?post as:name ?c .
}where{
    ?post a blog:LlogPost .
    ?post as:tag "eat" .
    ?post as:content ?c .
}

```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .

delete {
    ?post as:startTime ?st .
    ?post as:endTime ?et .
    ?post as:content ?c .
    ?post a blog:LlogPost .
}where{
    ?post a blog:Consumption .
    ?post as:endTime ?et .
    ?post as:content ?c .
    ?post as:startTime ?st .

}

```

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .

delete {
    ?post a blog:LlogPost .
}where{
    ?post a blog:Sleep .
}

```

[x] blog:follow_of -> Follow

```
@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix blog: <http://vocab.amy.so/blog#> .
insert into <http://blog.rhiaro.co.uk#> {
    ?post a as:Follow .
    ?post as:object ?object .
} where {
    ?post blog:follow_of ?object .   
}
```

```
@prefix blog: <http://vocab.amy.so/blog#> .
delete {
    ?post blog:follow_of ?object .
    ?post as:content ?c .
}where{
    ?post a as:Follow .
    ?post as:content ?c .
}
```

> Checkin 20160807-2349

[x] Change Consumption and Acquisition to Consume and Acquire and blog namespace

```
prefix blog: <http://vocab.amy.so/blog#> .
prefix asext: <https://terms.rhiaro.co.uk/as#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s a asext:Consume .
}where{
    ?s a blog:Consumption.
}
```

```
prefix blog: <http://vocab.amy.so/blog#> .
prefix asext: <https://terms.rhiaro.co.uk/as#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s a asext:Acquire .
}where{
    ?s a blog:Acquisition.
}
```

```
prefix blog: <http://vocab.amy.so/blog#> .
prefix asext: <https://terms.rhiaro.co.uk/as#> .

insert into <http://blog.rhiaro.co.uk#> {
    ?s asext:cost ?c .
}where{
    ?s blog:cost ?c .
}
```

```
prefix blog: <http://vocab.amy.so/blog#> .
prefix asext: <https://terms.rhiaro.co.uk/as#> .

delete {
    ?s1 a blog:Acquisition .
    ?s2 a blog:Consumption .
    ?s3 blog:cost ?c .
}
```

> Checkin: 20160815-1613

[x] All content from markdown to html

> Checkin: 20160822-1957
 
[x] -> I think I need a script+UI for type adding
[ ] people to as:Profile or as:Person or as:Actor
[ ] Add a as:Article if as:name and no other type
[ ] Add a as:Note if no name and no other type
[ ] Old posts... to Travel, Arrive, etc

> Checkin: up to http://llog.rhiaro.co.uk/1429108500-2 offset=440 at 20160808-0002
> 
> Checkin: up to http://llog.rhiaro.co.uk/1430007600-2 offset=500 at 20160808-0948

[ ] All tags to proper as:Objects
    -> uri() not a thing in 1.0
```
@prefix as: <http://www.w3.org/ns/activitystreams#> .

insert into <http://blog.rhiaro.co.uk#> {
    uri(fn:concat("http://uri2.com/#", "tag")) a as:Object .
    uri(fn:concat("http://uri2.com/#", "tag")) as:name ?tag .
    ?post as:tag uri(fn:concat("http://uri2.com/#", "tag")) .
} where {
    ?post as:tag ?tag .    
}
```

[ ] Make Collections for
* Travel
* Calendar
* stuff
* years (contains month collections only?)
* months
* where
* food
* All replies on individual posts
* mentions (contains individual post reply collections + homepage mentions?)

[ ] Put everything in its own graph
-> I think I need a script+UI for graph sorting..
