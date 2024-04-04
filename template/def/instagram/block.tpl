<div class="instagram">
<h4>Последние фотографии в Instagram</h4>
<ul>
{% for item in data %}
<li><a href="{{ item.href }}"><img data-src="{{ item.src }}" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="{{ item.title }}" title="{{ item.title }}"/></a></li>  
{% endfor %}  
</ul>
</div>