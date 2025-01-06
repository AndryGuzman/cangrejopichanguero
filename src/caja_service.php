<?php
class CajaService {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function verificarSesionAbierta($id_caja, $turno) {
        $stmt = $this->conexion->prepare("
            SELECT COUNT(*) AS total 
            FROM caja_sesion 
            WHERE id_caja = ? AND turno = ? AND estado = 'ABIERTO'
        ");
        $stmt->bind_param("is", $id_caja, $turno);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    public function abrirSesionCaja($id_caja, $turno, $monto_apertura, $id_usuario) {
        $stmt = $this->conexion->prepare("
            INSERT INTO caja_sesion (id_caja, turno, fecha_apertura, estado, monto_apertura, id_usuario)
            VALUES (?, ?, NOW(), 'ABIERTO', ?, ?)
        ");
        $stmt->bind_param("isdi", $id_caja, $turno, $monto_apertura, $id_usuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function obtenerSesionesAbiertas() {
        $query = "
            SELECT 
                cs.id, 
                c.nombre AS caja, 
                cs.turno, 
                cs.fecha_apertura, 
                cs.monto_apertura, 
                cs.monto_apertura + COALESCE(SUM(p.total), 0) AS saldo_actual
            FROM caja_sesion cs
            JOIN caja c ON cs.id_caja = c.id
            LEFT JOIN pago pa ON pa.id_sesion = cs.id
            LEFT JOIN pedidos p ON pa.id_pedido = p.id
            WHERE cs.estado = 'ABIERTO'
            GROUP BY cs.id, c.nombre, cs.turno, cs.fecha_apertura, cs.monto_apertura
            ORDER BY cs.fecha_apertura ASC
        ";
        return $this->conexion->query($query);
    }

    public function calcularMontoActual($id_sesion) {
        $sql_calculo = "
            SELECT 
                cs.id, 
                c.id AS 'caja', 
                (cs.monto_apertura + 
                 COALESCE(SUM(p.total), 0) - 
                 (SELECT COALESCE(SUM(aj.monto), 0) 
                  FROM ajustes_caja aj 
                  WHERE aj.id_sesion = cs.id)) AS monto_actual
            FROM caja_sesion cs
            INNER JOIN caja c ON c.id = cs.id_caja
            LEFT JOIN pago pa ON pa.id_sesion = cs.id
            LEFT JOIN pedidos p ON pa.id_pedido = p.id
            WHERE cs.id = ? AND cs.estado = 'ABIERTO'
            GROUP BY cs.id, c.id, cs.monto_apertura
        ";
        $stmt = $this->conexion->prepare($sql_calculo);
        $stmt->bind_param("i", $id_sesion);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function cerrarSesionCaja($id_sesion, $monto_actual) {
        $stmt = $this->conexion->prepare("
            UPDATE caja_sesion 
            SET fecha_cierre = NOW(), monto_cierre = ?, estado = 'CERRADO'
            WHERE id = ? AND estado = 'ABIERTO'
        ");
        $stmt->bind_param("di", $monto_actual, $id_sesion);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function obtenerSesionesCerradas($filtros = null) {
        $where_clauses = ["cs.estado = 'CERRADO'"];
        
        if ($filtros) {
            if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
                $where_clauses[] = "cs.fecha_cierre BETWEEN '{$filtros['desde']} 00:00:00' AND '{$filtros['hasta']} 23:59:59'";
            }
            if (!empty($filtros['turno']) && $filtros['turno'] !== 'TODOS') {
                $where_clauses[] = "cs.turno = '{$filtros['turno']}'";
            }
        }

        $query = "
            SELECT cs.id, c.nombre AS caja, cs.turno, cs.fecha_apertura, cs.fecha_cierre, 
                   cs.monto_apertura, cs.monto_cierre
            FROM caja_sesion cs
            JOIN caja c ON cs.id_caja = c.id
            WHERE " . implode(" AND ", $where_clauses) . "
            ORDER BY cs.fecha_cierre DESC
            LIMIT 3
        ";
        return $this->conexion->query($query);
    }

    public function obtenerCajas() {
        return $this->conexion->query("SELECT * FROM caja");
    }

    public function registrarAjusteCaja($id_sesion, $tipo_ajuste, $monto_ajuste, $descripcion_ajuste) {
        $stmt = $this->conexion->prepare("
            INSERT INTO ajustes_caja (id_sesion, tipo_ajuste, monto, descripcion)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isds", $id_sesion, $tipo_ajuste, $monto_ajuste, $descripcion_ajuste);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}