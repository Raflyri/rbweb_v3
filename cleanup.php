<?php
try { Schema::table('articles', function($table) { $table->dropIndex('articles_status_published_at_index'); }); echo "Dropped status index\n"; } catch(\Exception $e) { echo $e->getMessage()."\n"; }
try { Schema::table('articles', function($table) { $table->dropColumn(['title_en', 'title_id', 'content_en', 'content_id']); }); echo "Dropped columns\n"; } catch(\Exception $e) { echo $e->getMessage()."\n"; }
try { Schema::table('articles', function($table) { $table->dropIndex('articles_fulltext_index'); }); echo "Dropped ft index\n"; } catch(\Exception $e) { echo $e->getMessage()."\n"; }
