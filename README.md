Extbase Tweaks
==============

This extension offers several alternative implementations for TYPO3 Extbase
concerning localization and workspace overlays of elements. It was tested with
TYPO3 4.7.0, however might work with prior versions down to TYPO3 4.5.0 as well.

Language queries
----------------

The following TypoScript setting will effect in queries using the current
language during frontend rendering, instead of fetching default language and
then fetching the translated overlays for each record.

The result is, that one can have element in e.g. German that don't have a
localization parent element in English (default language).

```ruby
plugin.tx_news {
	persistence {
		classes {
			Tx_News_Domain_Model_News {
				query.useCurrentLanguage = 1
			}
		}
	}
}
```