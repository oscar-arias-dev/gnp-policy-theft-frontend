<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>GNP</title>
    <link href="themes/redmond/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
    <link href="Scripts/jtable/themes/lightcolor/blue/jtable.css" rel="stylesheet" type="text/css" />
    <script src="scripts/jquery-1.6.4.min.js" type="text/javascript"></script>
    <script src="scripts/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
    <script src="Scripts/jtable/jquery.jtable.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div id="PoliciesTableContainer" style="width: 100%;"></div>
    <script type="text/javascript">
        function actualizarEstado(id, codSinisestro, estado, selectElement) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Se actualizará el estado en la base de datos',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'PolicyActions.php?action=update',
                        type: 'GET',
                        data: {
                            id,
                            codSinisestro,
                            estado
                        },
                        success: function(response) {
                            if (!response || response === "") {
                                Swal.fire('¡Actualizado!', 'El estado ha sido actualizado.', 'success');
                                $('#PoliciesTableContainer').jtable('reload');
                                return;
                            }
                            const parsedResponse = JSON?.parse(response) ?? null;
                            const notificationType = (!parsedResponse || parsedResponse?.Result === "ERROR") ? 'warning' : 'success';
                            const notiHeader = (!parsedResponse || parsedResponse?.Result === "ERROR") ? 'Error :c' : 'Actualizado!';
                            const notiMessage = (!parsedResponse || parsedResponse?.Result === "ERROR") ? `No se pudo actualizar el estado: ${parsedResponse?.Message ?? "Error"}` : 'Actualizado!';
                            Swal.fire(notiHeader, notiMessage, notificationType);
                            if (parsedResponse?.Result !== "ERROR") {
                                $('#PoliciesTableContainer').jtable('reload');
                            } else {
                                selectElement.value = selectElement.getAttribute('data-original-value');
                            }
                        },
                        error: function(error) {
                            Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                        }
                    });
                } else {
                    selectElement.value = selectElement.getAttribute('data-original-value');
                }
            });
        }

        $(document).ready(function() {
            $('#PoliciesTableContainer').jtable({
                title: 'Pólizas',
                actions: {
                    listAction: 'PolicyActions.php?action=list',
                },

                fields: {
                    id: {
                        key: true,
                        create: false,
                        edit: false,
                        list: false
                    },
                    vin: {
                        title: 'VIN',
                    },
                    placas: {
                        title: 'Placa',
                    },
                    status_robo: {
                        title: 'Status de robo',
                    },
                    motum_status: {
                        title: 'MOTUM Status',
                    },
                    motum_vin: {
                        title: 'MOTUM VIN',
                    },
                    codigo_siniestro: {
                        title: 'Siniestro',
                    },
                    fecha_siniestro: {
                        title: 'Fecha Siniestro',
                    },
                    uuid: {
                        title: 'UUID',
                    },
                    tipo_vehiculo: {
                        title: 'Tipo',
                    },
                    clasificacion_vehiculo: {
                        title: 'Clasificación',
                    },
                    marca_vehiculo: {
                        title: 'Marca',
                    },
                    submarca_vehiculo: {
                        title: 'Submarca',
                    },
                    estado_circulacion: {
                        title: 'Estado',
                    },
                    modelo_vehiculo: {
                        title: 'Modelo',
                    },
                    created_at: {
                        title: 'Registro interno',
                    },
                    action: {
                        title: 'Estado',
                        sorting: false,
                        width: '15%',
                        display: function(data) {
                            const currentClave = data?.record?.status_robo?.split("-")?.[0] ?? "";
                            const filtered = [{
                                    clave: 'RR',
                                    descripcion: 'Reporte de Robo'
                                },
                                {
                                    clave: 'EO',
                                    descripcion: 'En operativo'
                                },
                                {
                                    clave: 'LO',
                                    descripcion: 'Localizado'
                                },
                                {
                                    clave: 'RE',
                                    descripcion: 'Recuperado'
                                },
                                {
                                    clave: 'NR',
                                    descripcion: 'No recuperado'
                                },
                                {
                                    clave: 'CA',
                                    descripcion: 'Cancelado'
                                }
                            ];
                            let options = filtered;
                            let isMotumVim = data?.record?.motum_vin?.toString() === "1" ? "" : "disabled";
                            let select = '<select ' + isMotumVim + ' data-original-value="' + currentClave + '" onchange="actualizarEstado(\'' + data.record.id + '\', \'' + data.record.codigo_siniestro + '\', this.value, this)">';
                            options.forEach(opt => {
                                const selectedValue = opt.clave === currentClave ? "selected" : "";
                                select += '<option ' + selectedValue + ' value="' + opt.clave + '">' + opt.descripcion + '</option>';
                            });
                            select += '</select>';
                            return select;
                        }
                    }
                }
            });
            $('#PoliciesTableContainer').jtable('load');
        });
    </script>

</body>

</html>