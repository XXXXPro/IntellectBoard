<div class="prating">
{% if not post.norate %}<a href="rate.htm?p={{ post.id }}&amp;d=pro"><i class="far fa-thumbs-up"></i></a>
{% else %}<span class="norate" title="{{ post.norate }}"><i class="far fa-thumbs-up"></i></span>
{% endif %}
<span class="prvalue"> {{ post.rating }} </span>
{% if forum.rate!=2 %}
{% if not post.norate %}<a href="rate.htm?p={{ post.id }}&amp;d=contra"><i class="far fa-thumbs-down"></i></a>
{% else %}<span class="norate" title="{{ post.norate }}"><i class="far fa-thumbs-down"></i></span>
{% endif %}{% endif %}
</div>