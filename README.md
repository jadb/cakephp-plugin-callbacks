# Plugin Callbacks

Enables callbacks in [CakePHP][1] plugins' models for other than the default Cake defined
methods (save, delete, etc.), allowing developers that use a certain plugin to extend it's
Models's methods (the ones that allow for that - defined by the plugins' developer)

## Installation

After cloning the repo in your `APP/plugins`, create the `callbacks` folder inside `APP/models`.

	mkdir models/callbacks

This is the place where to store all defined callbacks for the app.

## Configuration

A callback file is a file located inside APP/models/callbacks/, or
APP/plugins/ANOTHER_PLUGIN/models/callbacks/ and follows the below
naming convention:

Inside app:

	File: app_{my_plugin}.php
	Class: AppMyPluginCallbacks

or inside another plugin:

	File: another_plugin_{my_plugin}.php
	Class: AnotherPluginMyPluginCallbacks

In the above examples, the plugin requesting the callbacks name is `my_plugin`.
In the second example, the other plugin's name is `another_plugin`

Callback methods are defined using the following convention:

	onPluginModelNameDefinedPluginCallbackName

In this example, the plugin's model requesting the callback is `PluginModelName`
and the defined callback is `DefinedPluginCallbackName`

As a plugin developer, to enable a `beforeRefund` callback to be called from the app or
any other plugin for `billing` model `Invoice`, add the following line at the beginning of
`Invoice::refund()`:

	$this->Behaviors->Callbacks->run($this, 'beforeRefund', $data);

This will tell the plugin to trigger all `onInvoiceBeforeRefund` callbacks defined. According
to the above example, a callback can be defined in the `app` as follow:

	File: app_billing.php
	Class: AppBillingCallbacks
	Method: onInvoiceBeforeRefund

or in `marketing` plugin like this:

	File: marketing_billing.php
	Class: MarketingBillingCallbacks
	Method: onInvoiceBeforeRefund


## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

## Bugs & Feedback

http://github.com/jadb/cakephp-plugin-callbacks

[1]: http://cakephp.org "CakePHP"