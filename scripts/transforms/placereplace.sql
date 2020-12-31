prefix as: <https://www.w3.org/ns/activitystreams#> .
prefix owl: <http://www.w3.org/2002/07/owl#> .

insert into <https://blog.rhiaro.co.uk/> {
    ?s as:origin ?neworigin .
    ?s as:target ?newtarget .
} where {
    ?neworigin a as:Place .
    ?newtarget a as:Place .
    ?s as:origin ?origin .
    ?s as:target ?target .
    ?neworigin owl:sameAs ?origin .
    ?newtarget owl:sameAs ?target .
}



delete from <https://blog.rhiaro.co.uk/> {
    ?s as:origin ?origin .
    ?s as:target ?target .
} where {
    ?neworigin a as:Place .
    ?newtarget a as:Place .
    ?s as:origin ?origin .
    ?s as:origin ?neworigin .
    ?s as:target ?target .
    ?s as:target ?newtarget .
    ?neworigin owl:sameAs ?origin .
    ?newtarget owl:sameAs ?target .
}



select ?s ?neworigin ?origin ?newtarget ?target where {
    ?neworigin a as:Place .
    ?newtarget a as:Place .
    ?s as:origin ?origin .
    ?s as:target ?target .
    ?neworigin owl:sameAs ?origin .
    ?newtarget owl:sameAs ?target .
}