{% import 'macro.tpl' as macros %}
  <fieldset><legend>Информация о пользователе</legend>
    <div><span>Пол</span>{{ macros.radio('basic[gender]',{'U':'Не хочу указывать','M':'Мужской','F':'Женский'},formdata.basic.gender) }}</div>
    <div><label><span>Дата рождения (в формате ГГГГ-ММ-ДД)</span>{{ macros.input('basic[birthdate]',formdata.basic.birthdate|date('Y-m-d'),14,14) }}</label></div>
    <div><span>Показ даты рождения</span>{{ macros.radio('settings[show_birthdate]',{0:'Не показывать вообще',1:'Показывать только дату',2:'Показывать только возраст',3:'Показывать полностью'},formdata.settings.show_birthdate) }}</div>
    <div><label><span>Место жительства</span>{{ macros.input('basic[location]',formdata.basic.location,32) }}</label></div>
  </fieldset>
