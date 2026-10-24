<?php
// app_core/modules/notes/controllers/NotesController.php

require_once __DIR__ . '/../../../config/database.php';

class NotesController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllNotes() {
        $stmt = $this->db->query("SELECT * FROM operational_notes ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getNoteById($id) {
        $stmt = $this->db->prepare("SELECT * FROM operational_notes WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function saveNote($data) {
        $title = trim($data['title']);
        $category = trim($data['category'] ?? 'General');
        $content = trim($data['content']);
        $priority = $data['priority'] ?? 'Medium';
        $status = $data['status'] ?? 'Pending';

        if (empty($title) || empty($content)) return false;

        if (!empty($data['note_id'])) {
            $stmt = $this->db->prepare("UPDATE operational_notes SET title = ?, category = ?, content = ?, priority = ?, status = ? WHERE id = ?");
            return $stmt->execute([$title, $category, $content, $priority, $status, (int)$data['note_id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO operational_notes (title, category, content, priority, status) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$title, $category, $content, $priority, $status]);
        }
    }

    public function deleteNote($id) {
        $stmt = $this->db->prepare("DELETE FROM operational_notes WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}