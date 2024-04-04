<!DOCTYPE html>
<html>
<head>
<title>{{ intb.title }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css"><!--
html { font-size: 100.01%; height: 100%; overflow: hidden }
body { padding: 0; margin: 0; font-size: 62.5%; color: #362b36; height: 100% }
div { padding: 0; margin: 0 }
img { border: 0; padding: 0; margin: 0 }
td { vertical-align: top; padding: 4px }
form { padding: 0; margin: 0 }
ul { padding: 0; margin: 0; vertical-align: top; list-style-position: inside }
li { padding: 0; margin: 0 }
form { padding: 0; margin: 0 }
fieldset { padding: 0; margin: 0 }
select { margin: 2px 0 }

#ib_all { position: relative; width: 56%; margin: 20% auto; border: #ccc 1px; background: #F8F8F8 }
#ib_all h1 { padding: 10px 15px; font-size: 1.8em; margin: -1px; text-align: center }
#ib_all #content { padding: 15px; text-align: center; font-size: 1.4em }
#ib_all #link { text-align: center; font-weight: bold }
--></style>
{% if location and not noredirect %}
<meta http-equiv="refresh" content="5; url={{ location }}">
{% endif %}
</head>
<body>
<div id="ib_all">
<h1>{{ intb.title }}</h1>
<div id="content">
{% for item in intb.messages %}
<div>
{{ item.text|raw }}
</div>
{% endfor %}
{% if location %}
<div id="link"><a href="{{ location }}">{{ location_text }}</a></div>
{% endif %}
</div>
</div>
<!--##DEBUG#-->
</body>
</html>