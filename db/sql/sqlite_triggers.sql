
CREATE VIRTUAL TABLE "ib_post_fts" USING FTS5(txt, content='', tokenize="porter");
INSERT INTO "ib_post_fts" (rowid, txt) SELECT oid, data FROM "ib_text" WHERE type=16;

CREATE TRIGGER "ib_post_ai" AFTER INSERT ON "ib_text" WHEN new.type=16 BEGIN 
  INSERT INTO "ib_post_fts"(rowid, txt) VALUES (new.oid, new.data);  
END;
CREATE TRIGGER "ib_post_ad" AFTER DELETE ON "ib_text" WHEN old.type=16 BEGIN
  INSERT INTO "ib_post_fts"("ib_post_fts", rowid, txt) VALUES('delete', old.id, old.data);  
END;
CREATE TRIGGER "ib_post_au" AFTER UPDATE ON "ib_text" WHEN new.type=16 BEGIN
  INSERT INTO "ib_post_fts"("ib_post_fts", rowid, txt) VALUES('delete', old.id, old.data);  
  INSERT INTO "ib_post_fts"(rowid, txt) VALUES (new.oid, new.data);  
END;


CREATE VIRTUAL TABLE "ib_topic_fts" USING FTS5(title, descr, content="ib_topic", content_rowid="id", tokenize="porter");

CREATE TRIGGER "ib_topic_ai" AFTER INSERT ON "ib_topic" BEGIN
  INSERT INTO "ib_topic_fts"(rowid, title, descr) VALUES (new.id, new.title, new.descr);  
END;
CREATE TRIGGER "ib_topic_ad" AFTER DELETE ON "ib_topic" BEGIN
  INSERT INTO "ib_topic_fts"("ib_topic_fts", rowid, title, descr) VALUES('delete', old.id, old.title, old.descr);  
END;
CREATE TRIGGER "ib_topic_au" AFTER UPDATE ON "ib_topic" BEGIN
  INSERT INTO "ib_topic_fts"("ib_topic_fts", rowid, title, descr) VALUES('delete', old.id, old.title, old.descr);  
  INSERT INTO "ib_topic_fts"(rowid, title, descr) VALUES (new.id, new.title, new.descr);  
END;
