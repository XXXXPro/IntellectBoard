<!DOCTYPE html>

<head>
<link rel="stylesheet" type="text/css" href="{{ style('s.css') }}"/>
<link rel="preload" as="style" type="text/css" href="{{ style('post.css') }}" onload="this.rel='stylesheet'"/>
<style>
#ib_all { padding: 0 5px }
</style>
<title>Предварительный просмотр сообщения</title>
</head>
<body>
<div id="ib_all">
<div id="stdforum_view_topic"  class="forum{{ forum.id }} topic{{ topic.id }}">
<h1>{{ topic.title }}</h1>
<p class="descr">{{ topic.descr }}</p>
<div class="posts">
{% include 'stdforum/p_item.tpl' %}
</div>
</div>
<!--##DEBUG#-->
</div>
</body>
