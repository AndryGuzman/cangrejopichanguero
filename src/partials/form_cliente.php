<div class="form-group">
    <label for="documento">DNI</label>
    <input type="text" name="documento" value="<?php echo $cliente['documento']; ?>" class="form-control" required>
</div>
<div class="form-group">
    <label for="nombres">Nombres</label>
    <input type="text" name="nombres" value="<?php echo $cliente['nombres']; ?>" class="form-control" required>
</div>
<div class="form-group">
    <label for="apellidos">Apellidos</label>
    <input type="text" name="apellidos" value="<?php echo $cliente['apellidos']; ?>" class="form-control" required>
</div>
<div class="form-group">
    <label for="direccion">Dirección</label>
    <input type="text" name="direccion" value="<?php echo $cliente['direccion']; ?>" class="form-control">
</div>
<div class="form-group">
    <label for="celular">Celular</label>
    <input type="text" name="celular" value="<?php echo $cliente['celular']; ?>" class="form-control">
</div>
<div class="form-group">
    <label for="sexo">Sexo</label>
    <select name="sexo" class="form-control" required>
        <option value="M" <?php echo $cliente['sexo'] == 'M' ? 'selected' : ''; ?>>Masculino</option>
        <option value="F" <?php echo $cliente['sexo'] == 'F' ? 'selected' : ''; ?>>Femenino</option>
    </select>
</div>
<div class="form-group">
    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
    <input type="date" name="fecha_nacimiento" value="<?php echo $cliente['fecha_nacimiento']; ?>" class="form-control" required>
</div>
