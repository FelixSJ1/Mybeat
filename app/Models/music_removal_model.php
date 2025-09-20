<?php
class Music_Removal_Model {
    private $seed = [
        1 => ['id'=>1, 'title'=>'Música 1', 'artist'=>'Artista 1'],
        2 => ['id'=>2, 'title'=>'Música 2', 'artist'=>'Artista 2'],
        3 => ['id'=>3, 'title'=>'Música 3', 'artist'=>'Artista 3'],
    ];

    public function all() {
        return array_values($this->seed);
    }

    public function find($id) {
        $id = (int)$id;
        return isset($this->seed[$id]) ? $this->seed[$id] : null;
    }

    // placeholder: retorna sucesso, não altera dados
    public function delete($id) {
        return true;
    }
}
