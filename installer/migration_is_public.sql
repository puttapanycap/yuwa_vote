-- Migration: Add is_public column to vote_topics table
-- This allows topics to be viewed publicly without login

ALTER TABLE `vote_topics` 
ADD COLUMN `is_public` TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Whether the topic monitor page is publicly accessible (1=public, 0=private)' 
AFTER `show_score`;
