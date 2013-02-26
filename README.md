t3ext-previewrecords
====================

This is a nice configurable way to allow custom preview URLs when "edit&amp;view" TCEforms records.


You have to set those two lines in the pageTsconfig of your Sys-Folder:

TCEMAIN.tt_news.saveAndViewPageId = 13
TCEMAIN.tt_news.saveAndViewAdditionalParams = &tt_news[tx_ttnews]={field:uid}