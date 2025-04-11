<?php
    include 'Database\database.php';
    include 'public\header.php';
?>

<!DOCTYPE html>
<html lang="es">
<body>

    <div class="container mt-5">
        <h2 class="text-center">REGISTRO DE USUARIOS</h2>
        <br>
        
        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-header">
                <button type="button" class="btn-add-user" 
                    data-bs-toggle="modal" 
                    data-bs-target="#saveModal">
                    Agregar Usuario
                </button>
            </div>
            <div class="card-body">
            <table id="userTable" class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>

        
            <?php
            $result = $conn->query("SELECT * FROM users"); //SELECCIONANDO TODOS LOS REGISTROS DE LA BASE DE DATOS
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['ID']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <button type="button" class="btn btn-secondary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal" 
                                data-id="<?php echo $row['ID']; ?>"
                                data-nombre="<?php echo $row['nombre']; ?>"
                                data-email="<?php echo $row['email']; ?>">
                            Editar
                        </button>
                        <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['ID']; ?>)">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        </table>

            </div>
        </div>
    </div>

        <!-- Modal de Registro de datos -->
    <div class="modal fade" id="saveModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveModalLabel">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="save_nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="save_nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="save_email" class="form-label">Correo</label>
                            <input type="email" name="email" id="save_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="save" class="btn btn-primary">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <!-- Modal de Edición -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Correo</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update" class="btn btn-warning">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <!--Link Sweet alert-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!--Code JS-->
    <script src="public\script.js"></script>
</body>
</html>
<?php $conn->close(); ?>
