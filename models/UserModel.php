<?php
// models/UserModel.php (CORREGIDO)

class UserModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todos los usuarios de la base de datos, excluyendo la contraseña.
     * @return array Lista de usuarios o array vacío si no hay.
     */
    public function getAllUsers() {
        // <-- ¡CORRECCIÓN DE BD! Usamos 'id_user' y le damos un alias 'id'
        $query = "SELECT id_user as id, nombre, username, rol FROM usuarios ORDER BY nombre ASC";
        $result = $this->db->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Error al obtener todos los usuarios: " . $this->db->error);
            return []; // Devuelve array vacío en caso de error
        }
    }

    /**
     * Obtiene un usuario específico por su ID.
     * @param int $id ID del usuario.
     * @return array|null Datos del usuario o null si no se encuentra o hay error.
     */
    public function getUserById($id) {
        // <-- ¡CORRECCIÓN DE BD! Usamos 'id_user' en el WHERE y alias 'id' en el SELECT
        $query = "SELECT id_user as id, nombre, username, rol FROM usuarios WHERE id_user = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result->fetch_assoc();
        } else {
             error_log("Error al preparar consulta getUserById: " . $this->db->error);
            return null;
        }
    }

    // Nota: Las funciones para crear, actualizar y eliminar usuarios
    // las manejaremos directamente en el UserController por simplicidad en este ejemplo,
    // pero en una aplicación más grande, podrían estar aquí.

}
?>