UPGRADE FROM 7.0 to 7.1
=======================

Cache
-----

 * Deprecate `CouchbaseBucketAdapter`, use `CouchbaseCollectionAdapter` instead

Console
-------

 * Do not render errors/exceptions when using `--quiet`/`SHELL_VERBOSITY=-1`.
   To view errors, either enable at least normal verbosity or use a logger.

FrameworkBundle
---------------

 * Mark classes `ConfigBuilderCacheWarmer`, `Router`, `SerializerCacheWarmer`, `TranslationsCacheWarmer`, `Translator` and `ValidatorCacheWarmer` as `final`

Messenger
---------

 * Make `#[AsMessageHandler]` final

SecurityBundle
--------------

 * Mark class `ExpressionCacheWarmer` as `final`

Translation
-----------

 * Mark class `DataCollectorTranslator` as `final`

TwigBundle
----------

 * Mark class `TemplateCacheWarmer` as `final`

Workflow
--------

 * Add method `getEnabledTransition()` to `WorkflowInterface`
