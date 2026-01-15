-- Migration: Add show_score column to vote_topics
-- Date: 2026-01-15
-- Purpose: Enable toggle visibility of scores on monitor page

ALTER TABLE `vote_topics` ADD COLUMN `show_score` TINYINT(1) DEFAULT 1 AFTER `share_key`;
