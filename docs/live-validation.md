Live validation
====

Unlike Nette forms this package does not provide any built-in javascript validation. However it is not too difficult to implement AJAX validation instead. This of course means that there will be some delay before any new value is validated and the user gets the appropriate error message. However it also means he will get the errors from server-only validation right away and not only after submitting the form. For example live validation from Nette won't show you that an account with given e-mail already exists. The AJAX validation for symfony forms will.

Template
----

You'll need to add some blocks to your form theme:

```
{% use 'bootstrap_3_horizontal_layout.html.twig' %}

{# Wrap errors by a placeholder element. #}
{% block form_errors -%}
	<div id="error__{{ id }}" class="error-container">
		{{- block('form_errors_content') -}}
	</div>
{%- endblock form_errors %}

{# Only show one error for each field. #}
{% block form_errors_content -%}
	{% if errors|length > 0 -%}
		{%- if form.parent -%}
			<span class="error-message">{{ errors[0].message }}</span>
		{%- else -%}
			<div class="alert alert-danger">
				<i class="fa fa-ban"></i>
				{{ errors[0].message }}
			</div>
		{%- endif -%}
	{%- endif -%}
{%- endblock form_errors_content %}
```

Javascript
----

You'll also need some custom javascript to send the ajax requests and render the errors. I've published my own solution in the example project [here](https://github.com/enumag/arachne-forms-example/blob/master/www/resources/validation.js). The script requires jQuery and nette.ajax.js. Also because it is written as a jQuery plugin you'll need to turn it on like this:

```js
$(function () {
	$('form').ajaxValidation();
});
```
