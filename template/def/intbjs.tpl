<script><!--
 window.IntB_params = {
   basedir:'{{ url('') }}',{%
   if draft_name %}draft: '{{ draft_name }}',{% endif %}{% if smiles %}
   emoticonsRoot : '{{ url('sm/') }}',
   emoticons : { {%
   if smiles.dropdown %}
    dropdown : { {% for item in smiles.dropdown %}"{{ item.code }}":"{{ item.file }}",{% endfor %} }, {% endif %}{%
   if smiles.more %}
    more : { {% for item in smiles.more %}"{{ item.code }}":"{{ item.file }}",{% endfor %} },{% endif %}{%
   if smiles.hidden %}
     hidden : { {% for item in smiles.hidden %}"{{ item.code }}":"{{ item.file }}",{% endfor %} },{%
   endif %}
   },{% endif %}
   wysiwyg: '{{ get_opt('wysiwyg','user') }}',
   longposts: {{ get_opt('longposts','user') }},
   jquery_cdn: '{{ jquery_cdn }}',
   upload_max_filesize:  {{ upload_max_filesize }},
   post_max_size: {{ post_max_size }},
   {% if max_file_uploads %}
   max_file_uploads: {{ max_file_uploads }},
   attach_max_x : {{ attach_max_x }},
   attach_max_y : {{ attach_max_y }},
   jpeg_qty : {{ jpeg_quality }}
   {% endif %}
  };
--></script>
<script src="{{ url(intb_js) }}" defer="defer"></script>