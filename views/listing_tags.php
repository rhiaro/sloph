<article>
    <h1><?=count($tags)?> tags</h1>
    <form>
        <p>
            <input type="text" placeholder="Search" name="q" id="tagsearch" /> 
            <input type="submit" value="Search" id="searchsubmit" />
        </p>
    </form>
    <ul class="tags" id="tagslist">
    <?foreach($tags as $uri => $tag):?>
        <li><a href="<?=$uri?>"><?=$tag['name']?> (<?=$tag['count']?>)</a></li>
    <?endforeach?>
    </ul>
</article>