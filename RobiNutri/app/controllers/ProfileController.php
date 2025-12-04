<?php
require_once 'app/models/ProfileModel.php';

class ProfileController {
    private $model;

    public function __construct() {
        $this->model = new ProfileModel();
    }

    //Mostrar página de configuración de perfiles
    public function index() {
        //Obtener todos los perfiles existentes
        $perfiles = $this->model->getAll();
        
        echo "<!-- DEBUG: Perfiles obtenidos: " . count($perfiles) . " -->";
        
        require_once 'app/views/profiles/configuracion_perfiles.php';
    }

    //Guardar nuevo perfil (vía AJAX)
    public function save() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateProfileData($_POST);
            
            if (empty($errors)) {
                //DETECTAR SI ES CREACIÓN O EDICIÓN ⭐⭐
                $id = $_POST['id'] ?? null;
                
                if ($id) {
                    //MODO EDICIÓN - Actualizar perfil existente
                    if ($this->model->update($id, $_POST)) {
                        $response = [
                            'success' => true,
                            'id' => $id,
                            'message' => 'Perfil actualizado exitosamente'
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Error al actualizar el perfil'
                        ];
                    }
                } else {
                    //MODO CREACIÓN - Crear nuevo perfil
                    if ($this->model->create($_POST)) {
                        $response = [
                            'success' => true,
                            'id' => $this->model->getLastInsertId(),
                            'message' => 'Perfil creado exitosamente'
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Error al crear el perfil en la base de datos'
                        ];
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errors
                ];
            }
            
            echo json_encode($response);
            exit();
        }
    }

    //Cargar datos de un perfil para edición
    public function load() {
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $perfil = $this->model->getById($id);
            if ($perfil) {
                echo json_encode([
                    'success' => true,
                    'perfil' => $perfil
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Perfil no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ID no especificado'
            ]);
        }
        exit();
    }

    public function loadAll() {
        header('Content-Type: application/json');
        
        $perfiles = $this->model->getAll();
        
        echo json_encode([
            'success' => true,
            'perfiles' => $perfiles
        ]);
        exit();
    }

    //Eliminar perfil
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            header('Content-Type: application/json');
            
            if ($id && $this->model->delete($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Perfil eliminado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el perfil'
                ]);
            }
        }
    }

    //Validar datos del perfil
    private function validateProfileData($data) {
        $errors = [];
        
        //Validar nombre
        if (empty(trim($data['nombre']))) {
            $errors['nombre'] = 'El nombre es obligatorio';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $data['nombre'])) {
            $errors['nombre'] = 'El nombre solo debe contener letras';
        }
        
        //Validar apellido
        if (empty(trim($data['apellido']))) {
            $errors['apellido'] = 'El apellido es obligatorio';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $data['apellido'])) {
            $errors['apellido'] = 'El apellido solo debe contener letras';
        }
        
        //Validar edad
        if (empty(trim($data['edad']))) {
            $errors['edad'] = 'La edad es obligatoria';
        } elseif (!is_numeric($data['edad']) || $data['edad'] < 1 || $data['edad'] > 120) {
            $errors['edad'] = 'La edad debe ser entre 1 y 120 años';
        }
        
        return $errors;
    }
}
?>