{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_stats">
<h1>График изменения основных показателей форума</h1>
<a href="stats.htm">&laquo; Вернуться к статистике</a>
<p>Отображать данные {% if timelimit %}<b>за последние 90 дней</b> <a href="?all_time=1">за все время</a>
{% else %}<a href="?">за последние 90 дней</a> <b>за все время</b>{% endif %}
<div id="graph" style="width:100%;height:540px"></div>
<div id="small" style="width:100%;height:150px"></div>
<script src="{{ url('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ url('js/flot/jquery.flot.min.js') }}" type="text/javascript"></script>
<script src="{{ url('js/flot/jquery.flot.time.min.js') }}" type="text/javascript"></script>
<script src="{{ url('js/flot/jquery.flot.selection.min.js') }}" type="text/javascript"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="{{ url('js/flot/excanvas.min.js') }}"></script><![endif]-->
<script type="text/javascript">
var data = [
{ label: "Регистрации пользователей", data: [ {% for item in udata %}[{{ item.uday }}000,{{ item.ucount }}]{% if not loop.last %},{% endif %}{% endfor %} ] }
{% if pdata|length>0 %}, { label: "Сообщения", data: [ {% for item in pdata %}[{{ item.pday }}000,{{ item.pcount }}]{% if not loop.last %},{% endif %}{% endfor %} ] }{% endif %}
{% if tdata|length>0 %}, { label: "Новые темы", data: [ {% for item in tdata %}[{{ item.tday }}000,{{ item.tcount }}]{% if not loop.last %},{% endif %}{% endfor %} ] }{% endif %}
{% if pmdata|length>0 %}, { label: "Личные сообщения", data: [ {% for item in pmdata %}[{{ item.pmday }}000,{{ item.pmcount }}]{% if not loop.last %},{% endif %}{% endfor %} ] }{% endif %}
];

$("<div id='tooltip'></div>").css({
    position: "absolute",
    display: "none",
    border: "1px solid #ffd",
    padding: "2px",
    "background-color": "#ffe",
    opacity: 0.80
  }).appendTo("body");

function weekendAreas(axes) {
    var markings = [],
      d = new Date(axes.xaxis.min);
    d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
    d.setUTCSeconds(0);
    d.setUTCMinutes(0);
    d.setUTCHours(0);
    var i = d.getTime();
    do {
      markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
      i += 7 * 24 * 60 * 60 * 1000;
    } while (i < axes.xaxis.max);
    return markings;
}

var options = {
      series: {
          lines: { show: true },
          points: { show: true }
      },
      xaxis: {
        mode: "time",
        timeformat: "%d.%m.%Y",
      },
      yaxis: { min: 0, tickDecimals: 0 },
      grid: {
        hoverable: true,
        markings: weekendAreas
      }
  };

var small_options = {
        series: {
            lines: { show: true, lineWidth: 1 },
            points: { show: false },
            shadowSize: 0
        },
        xaxis: {
          mode: "time", ticks: []
        },
        yaxis: { min: 0, tickDecimals: 0 },
        grid: {
          hoverable: false
        },
        selection: { mode: "x" },
        legend: { show: false }
    };

  var plot = $.plot('#graph', data, options);
  var overview = $.plot('#small', data, small_options);

  $("#graph").bind("plotselected", function (event, ranges) {

        // do the zooming
        $.each(plot.getXAxes(), function(_, axis) {
          var opts = axis.options;
          opts.min = ranges.xaxis.from;
          opts.max = ranges.xaxis.to;
        });
        plot.setupGrid();
        plot.draw();
        plot.clearSelection();

        // don't fire event on the overview to prevent eternal loop

        overview.setSelection(ranges, true);
  });
    $("#graph").bind("plothover", function (event, pos, item) {
          if (item) {
            var x = item.datapoint[0].toFixed(2),
              y = item.datapoint[1].toFixed(2);

            var d = new Date(parseInt(x));
            $("#tooltip").html( d.toLocaleString() + " <br />" + item.series.label + ': '+ parseInt(y))
              .css({top: item.pageY+5, left: item.pageX+5})
              .fadeIn(200);
          } else {
            $("#tooltip").hide();
          }
      });

  $("#small").bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
        console.log('Done!');
  });
</script>
<h3>Рекорды форума</h3>
<table class="ibtable" style="width: 50%"><col style="width: 40%"/><col style="width: 20%"/><col />
<thead><tr><th>Показатель</th><th>Значение</th><th>Дата</th></tr></thead>
<tbody>
<tr><td>Регистрации пользователей</td><td style="text-align: center">{{ urecord.ucount }}</td><td>{{ urecord.uday|longdate }}</td></tr>
<tr><td>Сообщения</td><td style="text-align: center">{{ precord.pcount }}</td><td>{{ precord.pday|longdate }}</td></tr>
<tr><td>Новые темы</td><td style="text-align: center">{{ trecord.tcount }}</td><td>{{ trecord.tday|longdate }}</td></tr>
<tr><td>Личные сообщения</td><td style="text-align: center">{{ pmrecord.pmcount }}</td><td>{% if pmrecord.pmday %}{{ pmrecord.pmday|longdate }}{% endif %}</td></tr>
</tbody>
</table>
</div>
{% endblock %}
