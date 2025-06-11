<?php
date_default_timezone_set('America/Mexico_City');

// Obtener los contratos para listar arrendatarios y separarlos en dos grupos:
$tenants_local = [];
$tenants_trifasico = [];
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts?limit=0",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['DOLAPIKEY: web123456789']
]);
$result = curl_exec($curl);
curl_close($curl);

if ($result !== false) {
    $contracts = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        foreach ($contracts as $data) {
            // Consideramos sólo contratos activos y que tengan número de teléfono
            if ($data["nbofservicesopened"] < 1) continue;
            $telefono = $data["array_options"]["options_numero_de_telefono_"] ?? '';
            if (!empty($telefono)) {
                $localNumber = "";
                $tipo = "";
                // Primero se chequea si es trifacico
                if (stripos($data['ref_customer'], 'trifasico') !== false) {
                    $tipo = 'trifacico';
                    if (preg_match('/trifasico\s*(?:no\.?\s*|#)?(\d+)/i', $data['ref_customer'], $matches)) {
                        $localNumber = ltrim($matches[1], '0');
                    }
                } elseif (stripos($data['ref_customer'], 'local') !== false) {
                    $tipo = 'local';
                    if (preg_match('/local\s*(?:no\.?\s*|#)?(\d+)/i', $data['ref_customer'], $matches)) {
                        $localNumber = ltrim($matches[1], '0');
                    }
                } else {
                    $tipo = 'local'; // Por defecto, si no se encuentra el tipo
                }
                
                $tenant = [
                    'ref_customer' => $data['ref_customer'], // Ej. "Local No. 1 - Tienda X" o "Trifasicos No. 1 - Tienda Y"
                    'ref'          => $data['ref'],          // Ej. "CT2501-0001"
                    'telefono'     => $telefono,
                    'local_number' => $localNumber,          // Ej. "1"
                    'tipo'         => $tipo
                ];
                
                if ($tipo === 'trifacico') {
                    $tenants_trifasico[] = $tenant;
                } else {
                    $tenants_local[] = $tenant;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensaje Individual - Plaza Shopping Center</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-whatsapp {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px 0;
            background-color: #25D366;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-whatsapp:hover {
            background-color: #128C7E;
        }
        
        .btn-whatsapp i {
            margin-right: 5px;
        }
        
        #whatsappButtonContainer {
            margin-top: 15px;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-user"></i> Enviar Mensaje Individual</h1>
        </div>
        <div class="form-container">
            <form id="messageForm">
                <!-- Checklist para seleccionar tipo -->
                <div style="margin-bottom: 15px;">
                    <label>Selecciona el TIPO:</label><br>
                    <input type="radio" name="tipo_filtro" value="local" id="filtroLocal">
                    <label for="filtroLocal">LOCAL</label>
                    <input type="radio" name="tipo_filtro" value="trifacico" id="filtroTrifacico">
                    <label for="filtroTrifacico">TRIFACICO</label>
                </div>
                
                <!-- Campo de búsqueda -->
                <div style="margin-bottom: 15px;">
                    <label for="searchLocal">Buscar Local/Trifásicos:</label><br>
                    <input 
                        type="text" 
                        id="searchLocal" 
                        placeholder="Ejemplo: 05, 5, 12..."
                        style="width: 80%; margin-right: 10px;"
                    >
                    <button type="button" id="btnSearchLocal">Buscar</button>
                </div>
                
                <!-- Select para locales (excluye trifacicos) -->
                <div id="local_container" style="display:none;">
                    <label for="tenant_local">Selecciona un arrendatario (Local):</label>
                    <select name="telefono" id="tenant_local" class="tenant-select">
                        <option value="">-- Seleccione un arrendatario --</option>
                        <?php foreach ($tenants_local as $tenant): ?>
                            <option 
                                value="<?php echo htmlspecialchars($tenant['telefono']); ?>"
                                data-local="<?php echo htmlspecialchars($tenant['local_number']); ?>"
                                data-refcustomer="<?php echo htmlspecialchars($tenant['ref_customer']); ?>"
                            >
                                <?php echo htmlspecialchars($tenant['ref_customer'] . " (" . $tenant['ref'] . ")"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Select para trifacicos -->
                <div id="trifacico_container" style="display:none;">
                    <label for="tenant_trifacico">Selecciona un arrendatario (Trifacico):</label>
                    <select name="telefono" id="tenant_trifacico" class="tenant-select">
                        <option value="">-- Seleccione un arrendatario --</option>
                        <?php foreach ($tenants_trifasico as $tenant): ?>
                            <option 
                                value="<?php echo htmlspecialchars($tenant['telefono']); ?>"
                                data-local="<?php echo htmlspecialchars($tenant['local_number']); ?>"
                                data-refcustomer="<?php echo htmlspecialchars($tenant['ref_customer']); ?>"
                            >
                                <?php echo htmlspecialchars($tenant['ref_customer'] . " (" . $tenant['ref'] . ")"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="whatsappButtonContainer" style="margin-top: 20px; text-align: center; display: none;">
                    <a href="#" id="whatsappButton" class="btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i> Abrir Chat de WhatsApp
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Script para manejo del checklist, búsqueda y habilitación del select visible -->
    <script>
    // Referencias a los elementos
    const filtroRadios = document.getElementsByName('tipo_filtro');
    const localContainer = document.getElementById('local_container');
    const trifacicoContainer = document.getElementById('trifacico_container');
    const tenantLocal = document.getElementById('tenant_local');
    const tenantTrifacico = document.getElementById('tenant_trifacico');
    const whatsappButton = document.getElementById('whatsappButton');
    const whatsappButtonContainer = document.getElementById('whatsappButtonContainer');
    
    // Función para actualizar el enlace de WhatsApp
    function updateWhatsAppLink() {
        let selectedOption;
        let telefono = '';
        let refCustomer = '';
        
        if (localContainer.style.display !== 'none' && tenantLocal.selectedIndex > 0) {
            selectedOption = tenantLocal.options[tenantLocal.selectedIndex];
            telefono = selectedOption.value;
            refCustomer = selectedOption.getAttribute('data-refcustomer');
        } else if (trifacicoContainer.style.display !== 'none' && tenantTrifacico.selectedIndex > 0) {
            selectedOption = tenantTrifacico.options[tenantTrifacico.selectedIndex];
            telefono = selectedOption.value;
            refCustomer = selectedOption.getAttribute('data-refcustomer');
        }
        
        if (telefono) {
            // Limpiar el número de teléfono (eliminar espacios, paréntesis, guiones, etc.)
            let telefonoLimpio = telefono.replace(/[\s\(\)\-\+]/g, '');
            
            // Crear mensaje predeterminado
            let mensajePredeterminado = `Estimado arrendatario del ${refCustomer}, sus datos indican que necesitamos comunicarnos con usted.`;
            
            // Actualizar el enlace
            whatsappButton.href = `https://wa.me/${telefonoLimpio}?text=${encodeURIComponent(mensajePredeterminado)}`;
            whatsappButtonContainer.style.display = 'block';
        } else {
            whatsappButtonContainer.style.display = 'none';
        }
    }
    
    // Al cambiar el radio, mostrar el select correspondiente y deshabilitar el otro
    for (let radio of filtroRadios) {
        radio.addEventListener('change', function() {
            if (this.value === 'local') {
                localContainer.style.display = 'block';
                tenantLocal.disabled = false;
                trifacicoContainer.style.display = 'none';
                tenantTrifacico.disabled = true;
            } else if (this.value === 'trifacico') {
                trifacicoContainer.style.display = 'block';
                tenantTrifacico.disabled = false;
                localContainer.style.display = 'none';
                tenantLocal.disabled = true;
            }
            updateWhatsAppLink();
        });
    }
    
    // Actualizar el enlace cuando cambie la selección
    tenantLocal.addEventListener('change', updateWhatsAppLink);
    tenantTrifacico.addEventListener('change', updateWhatsAppLink);
    
    // Funcionalidad de búsqueda: aplica al select visible
    document.getElementById('btnSearchLocal').addEventListener('click', function() {
        let searchValue = document.getElementById('searchLocal').value.trim();
        if (/^\d+$/.test(searchValue)) {
            searchValue = parseInt(searchValue, 10).toString();
        }
        
        let visibleSelect;
        if (localContainer.style.display !== 'none') {
            visibleSelect = tenantLocal;
        } else if (trifacicoContainer.style.display !== 'none') {
            visibleSelect = tenantTrifacico;
        } else {
            alert('Por favor, selecciona un tipo primero.');
            return;
        }
        
        let found = false;
        for (let i = 0; i < visibleSelect.options.length; i++) {
            const option = visibleSelect.options[i];
            const localNum = (option.getAttribute('data-local') || "").replace(/^0+/, '');
            if (localNum === searchValue) {
                visibleSelect.selectedIndex = i;
                found = true;
                updateWhatsAppLink();
                break;
            }
        }
        
        if (!found) {
            alert('No se encontró el local o trifacico con ese número.');
        }
    });
    
    // Prevenir envío del formulario
    document.getElementById('messageForm').addEventListener('submit', function(event) {
        event.preventDefault();
    });
    </script>
</body>
</html>