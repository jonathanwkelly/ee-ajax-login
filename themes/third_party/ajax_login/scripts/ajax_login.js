
define(
	["jquery"], 
	function($)
	{
		"use strict";

		/* ========== README ========== 

			Ajax login forms are structured in this way:

				<form data-ajax-login>
					<input type="text" name="email">
					<input type="password" name="password">
				</form>

			[data-ajax-login] : this attribute is required as a minimum; it 
			identifies the form as being for ajax login.
		*/

		/* ========== private properties ========== */

		var 
			/*
			 * @var {object} _selectors
			 * Will hold strings that will be used as element selectors
			 */
			_selectors = 
			{
				form: "[data-ajax-login]"
			},

			/*
			 * @var {object} _elements
			 * Will hold arrays of jQuery objects
			 */
			_elements = 
			{
				$form: []
			},

			/*
			 * @var {function} _onError
			 * The callback function for when the login fails. The function 
			 * is passed an object of error messages, indexed with the 
			 * corresponding field name.
			 */
			_onError = function(msgs)
			{

			},

			/*
			 * @var {function} _onSuccess
			 * The callback function for when the login is a success; the function
			 * is passed the member data returned from the login call.
			 */
			_onSuccess = function(member)
			{

			}
		;

		/* ========== private methods ========== */

		/*
		 * Setup the functions of the ajax login form
		 * 
		 * @param {object} _paramArgs Pass in an array to override 
		 * the onError and onSuccess callbacks
		 * @return {void}
		 */
		var _init = function(_paramArgs)
		{
			// --- init the elements

			_init_elements();

			// --- override the default callbacks

			if(typeof _paramArgs === "object")
			{
				if(typeof _paramArgs.onError === "function")
				{
					_onError = _paramArgs.onError;
				}

				if(typeof _paramArgs.onSuccess === "function")
				{
					_onSuccess = _paramArgs.onSuccess;
				}
			}

			// --- attach the ajax form processing

			_elements.$form
				.unbind("submit.ajaxLogin")
				.on(
					"submit.ajaxLogin",
					function()
					{
						// --- do the AJAX call

						$.ajax({
							url: "/?ACT=" + _elements.$form.children('input[name="AID"]').val(),
							data: {
								email: _elements.$form.find('input[name="email"]').val(),
								password: _elements.$form.find('input[name="password"]').val(),
								password_confirm: _elements.$form.find('input[name="password_confirm"]').val(),
								XID: _elements.$form.children('input[name="XID"]').val()
							},
							type: _elements.$form.attr("method"),
							dataType: "json",
							success: function(json)
							{
								// --- success callback

								if(json.success === true)
								{
									if(typeof _onSuccess === "function")
									{
										_onSuccess.call(null, json.data);
									}
								}

								// --- failure callback
								else
								{
									if(typeof _onError === "function")
									{
										_onError.call(null, json.errors);
									}
								}
							}
						});

						return false;
					}
				);
		};

		// ---

		/*
		 * Handles the submission of the form to the server
		 * 
		 * @param {object} $form The form being processed
		 * @return {void}
		 */
		var _submit = function($form)
		{
			
		};

		/* ========== private methods, querying & filtering ========== */

		/*
		 * Get the jQuery elements into the _elements object
		 * 
		 * @param {void}
		 * @return {void}
		 */
		var _init_elements = function()
		{
			_elements.$form = $(_selectors.form);
		};

		// --- return the module

		return {
			init: _init
		};
	}
);