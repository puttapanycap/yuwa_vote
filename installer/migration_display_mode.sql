-- Migration: Add display_mode column to vote_topics
-- Date: 2026-01-15
-- Purpose: Enable List/Card view mode selection for monitor page
-- Values: 'card' = default card view, 'list' = list view

ALTER TABLE `vote_topics` ADD COLUMN `display_mode` VARCHAR(10) DEFAULT 'card' AFTER `show_score`;
