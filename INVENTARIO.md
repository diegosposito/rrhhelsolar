# Inventario — Sistema legacy (Symfony 1.4 + Doctrine)

Este documento es un análisis para modernización del sistema. El sistema actual fue
construido sobre un proyecto anterior, por lo que conviven artefactos de distintas épocas
y de más de un dominio. La columna **"¿Se usa?"** se deja intencionalmente vacía: la
completa el dueño del sistema, que es quien conoce el uso real en producción. Las
**Observaciones** son señales objetivas extraídas del esquema y del código (nombres,
sufijos, presencia/ausencia de modelo Doctrine, referencias entre módulos), **no**
decisiones de negocio sobre qué conservar o descartar.

Este inventario cubre **solo la app `alumnos`**. Otras apps del proyecto quedan fuera de
alcance.

---

## A. Módulos de la app alumnos

| Módulo | ¿Se usa? | Qué hace | Tablas/Modelos | Observaciones |
|---|---|---|---|---|
| alumat | | Relación alumno–materia (cursadas/notas) | AluMat, Alumnos, CarrerasSede | |
| alumnos | | Gestión central de alumnos (alta, legajo, documentación) | Alumnos, Personas, Areas, CiclosLectivos, Ciudades, DesignacionesEmpleados, DocumentacionAlumnos, DocumentacionPlanesEstudios, EmpleadosSede, Estudios, Facultades, MateriasPlanes, MesasExamenes, Paises, Provincias, Sedes, sfGuardUser, TiposDocumentos | Módulo grande y central |
| api | | Endpoints/API para consumo externo | AluMat, Alumnos, Calendarios, CarrerasSede, Catedras, CiclosLectivos, Comisiones, Correlatividades, DocumentacionAlumnos, EmpleadosSede, Encuestas, Examenes, FechasCalendario, InscripcionesCicloLectivo, MateriasPlanes, MesasExamenes, PeriodosCursadas, Personas, Solicitudes, sfGuardUser | |
| areadocumentos | | CRUD de áreas de documentos | AreaDocumentos | |
| areas | | CRUD de áreas | Areas | |
| areascargos | | CRUD de cargos por área | AreasCargos | |
| areascarrera | | CRUD de áreas de carrera | AreasCarrera | |
| asignaciones | | CRUD de asignaciones | Asignaciones | |
| asignacionesclases | | Asignación de clases a cátedras/comisiones | AsignacionesClases, Catedras, CiclosLectivos, Clases, Comisiones | |
| aspirante | | Preinscripción/aspirantes (ingreso) | Alumnos, CarrerasSede, CiclosLectivos, Ciudades, DocumentacionAlumnos, DocumentacionPlanesEstudios, Estudios, Facultades, Paises, Personas, PlanesEstudios, Provincias, TiposDocumentos, sfGuardUser | Solapa mucho con alumnos |
| auditoria | | Admin-generator sobre tabla de auditoría | Auditoria | Log de auditoría del sistema |
| aulas | | CRUD de aulas | Aulas, Edificios | |
| autogestion | | Autogestión del alumno (inscripción a mesas/exámenes) | Alumnos, Comisiones, Examenes, InscripcionesMesas, MesasExamenes | |
| autoridades | | CRUD de autoridades | Autoridades | |
| bajas | | Gestión de bajas de alumnos | Alumnos, BajasAlumnos, Facultades, PlanesEstudios, Sedes | |
| calendarios | | CRUD de calendarios académicos | Calendarios, Areas, FechasCalendario | |
| cargoautoridades | | CRUD de cargos de autoridades | CargoAutoridades | |
| carreras | | CRUD de carreras | Carreras, AreasCarrera | |
| carrerassede | | CRUD de carreras por sede | CarrerasSede, Carreras | |
| catedras | | CRUD de cátedras | Catedras, MateriasPlanes, PlanesEstudios, Sedes | |
| categoriastitulos | | CRUD de categorías de títulos | CategoriasTitulos | |
| centros | | CRUD de centros | Centros | |
| cicloslectivos | | Gestión de ciclos lectivos | CiclosLectivos, AluMat, Alumnos, Catedras, Comisiones, MateriasPlanes, MesasExamenes | |
| ciudades | | CRUD de ciudades | Ciudades | |
| clases | | CRUD de clases | Clases | |
| comisiones | | Gestión de comisiones | Comisiones, Catedras, MateriasPlanes, PlanesEstudios, Sedes | |
| condicionesmesas | | CRUD de condiciones de mesas | CondicionesMesas | |
| contactos | | CRUD de contactos | Contactos | |
| correlatividades | | Gestión de correlatividades | Correlatividades, PlanesEstudios | |
| dependencias | | CRUD de dependencias | Dependencias | |
| derivaciones | | Derivación/respuesta de expedientes entre áreas | ExpedientesDerivaciones, ExpedientesEgresados, Alumnos, Areas, AreasCarrera, sfGuardUser | Templates _formDerivar/responder |
| designaciones | | CRUD de designaciones | Designaciones | |
| designacionesempleados | | Designaciones de empleados | DesignacionesEmpleados, Empleados | |
| designacionesmesas | | Designaciones en mesas de examen | DesignacionesMesas | |
| detallehorarios | | CRUD de detalle de horarios | DetalleHorarios | |
| detallenota | | CRUD de detalle de notas | DetalleNota | |
| detalleplan | | CRUD de detalle de plan | DetallePlan | Modelo sin tabla en el dump |
| diplomas | | Solo executeIndex con un template | (ninguna tabla clara) | Módulo stub: 22 líneas, 1 acción |
| doclaboral | | CRUD de documentación laboral | DocLaboral | |
| documentacion | | CRUD de documentación | Documentacion | |
| documentacionplanes | | Documentación exigida por plan | DocumentacionPlanesEstudios, Documentacion, PlanesEstudios | |
| documentosinstitucion | | CRUD de documentos de la institución | DocumentosInstitucion | |
| edificios | | CRUD de edificios | Edificios, Sedes | |
| egresados | | Gestión de egresados | Alumnos, AreasCarrera, CiclosLectivos, ExpedientesEgresados, Facultades, Personas, PlanesEstudios, Sedes, TiposDocumentos | |
| empleados | | CRUD de empleados | Empleados, Personas | |
| empleadossede | | Empleados por sede | EmpleadosSede | |
| encuestas | | CRUD de encuestas | Encuestas | |
| encuestasalumnos | | Encuestas respondidas por alumnos | EncuestasAlumnos, Alumnos | |
| equivalencias | | Gestión de equivalencias de materias | EquivalenciasAlumnos, MateriasEquivalencias, MateriasPlanes, Alumnos, LibrosActas | MateriasEquivalencias: modelo sin tabla |
| escalasnotas | | CRUD de escalas de notas | EscalasNotas | |
| estadisticas | | Reportes/estadísticas académicas | AreasCarrera, Designaciones, Empleados, Facultades, PlanesEstudios, Sedes, TiposCarreras | |
| estadosalumno | | Gestión de estados del alumno (historial) | EstadosAlumno, EstadosAlumnoHistorial, AluMat, Alumnos, BajasAlumnos, Comisiones, DesignacionesEmpleados, PlanesEstudios, sfGuardUser | |
| estadoscarreras | | Admin-generator estados de carrera | EstadosCarreras | |
| estadoscomisiones | | CRUD de estados de comisiones | EstadosComisiones | |
| estadosmateria | | CRUD de estados de materia | EstadosMateria | |
| estadosplanes | | Admin-generator estados de planes | EstadosPlanes | |
| estudios | | CRUD de estudios (nivel educativo) | Estudios | |
| examenes | | Gestión de exámenes | Examenes, Alumnos | |
| expedientes | | Gestión de expedientes (títulos/diplomas) | ExpedientesEgresados, ExpedientesDerivaciones, Alumnos, Ciudades, Condiciones, CondicionesExpedientes, Documentacion, DocumentacionExpedientes, EmpleadosSede, Estudios, Paises, PlanesEstudios, Provincias, Titulos | |
| facultades | | CRUD de facultades | Facultades, PlanesEstudios | |
| fechascalendario | | Fechas del calendario / llamados | FechasCalendario, LlamadosTurno, MesasExamenes | |
| graficos | | Gráficos/reportes | AreasCarrera | |
| grupomenu | | Admin-generator de grupos de menú | grupomenu | Infraestructura de menú/UI |
| historialpaciente | | CRUD de historial de paciente | Historialpaciente | Término médico "paciente/historial" |
| horarios | | Gestión de horarios | Horarios, Personas | |
| impresiones | | Impresión de constancias/listados | AluMat, Alumnos, AsignacionesClases, Carreras, Catedras, Comisiones, DocumentacionAlumnos, EstadosMateria, MesasExamenes, PlanesEstudios, Sedes | |
| informes | | Informes/reportes académicos varios | Alumnos, Autoridades, Calendarios, Carreras, CiclosLectivos, Ciudades, Comisiones, Contactos, Correlatividades, DesignacionesEmpleados, EstadosAlumno, Horarios, InscripcionesCicloLectivo, MateriasPlanes, ObrasSociales, Personas, PlanesEstudios, Sedes, sfGuardUser | Referencia a ObrasSociales (dominio médico) |
| ingreso | | Proceso de ingreso | Areas | Poca lógica de tablas visible |
| inscripciones | | Inscripciones a cursadas/materias | AluMat, Alumnos, AsignacionesClases, CarrerasSede, Catedras, CiclosLectivos, Comisiones, Correlatividades, DocumentacionAlumnos, Encuestas, Examenes, MateriasPlanes, MesasExamenes, PlanesEstudios | |
| inscripcionesmesas | | Admin-generator inscripciones a mesas | InscripcionesMesas | |
| libredeuda | | Consulta de libre deuda / histórico | HistoricoConsultas | |
| librosactas | | CRUD de libros de actas | LibrosActas | |
| listahorarios | | CRUD de lista de horarios | ListaHorarios | |
| llamadosturno | | Gestión de llamados a turno de examen | LlamadosTurno, FechasCalendario, MesasExamenes | |
| materias | | CRUD de materias | Materias | |
| materiasgenericas | | CRUD de materias genéricas | MateriasGenericas | |
| materiasplanes | | Materias por plan de estudios | MateriasPlanes, CarrerasSede, Catedras, PlanesEstudios | |
| menu | | Admin-generator de menú | Menu | Infraestructura de menú/UI |
| mesasexamenes | | Gestión de mesas de examen | MesasExamenes, AluMat, Alumnos, Areas, Catedras, EstadosMesasExamenes, Examenes, LibrosActas, LlamadosTurno, MateriasPlanes, PlanesEstudios, TiposDesignacionesMesas | |
| modalidadescarreras | | Admin-generator modalidades de carrera | ModalidadesCarreras | |
| modosegreso | | Admin-generator modos de egreso | ModosEgreso | |
| modosevaluaciones | | CRUD de modos de evaluación | ModosEvaluaciones | |
| movimientosalumnos | | CRUD de movimientos de alumnos | MovimientosAlumnos | |
| nivelesestudios | | CRUD de niveles de estudios | NivelesEstudios | |
| noticias | | Noticias (por carrera) | Noticias, NoticiasCarrera | |
| obrassociales | | CRUD de obras sociales | ObrasSociales | Término médico "obra social" |
| paciente | | CRUD de pacientes + historial | Paciente, Historialpaciente | Término médico "paciente" |
| paises | | CRUD de países | Paises | |
| periodosciclos | | CRUD de períodos de ciclos | PeriodosCiclos, FechasCalendario | |
| periodoscursadas | | CRUD de períodos de cursada | PeriodosCursadas, FechasCalendario | |
| personas | | Gestión de personas (datos personales) | Personas, Alumnos, Areas, Ciudades, Estudios, MesesCobro, Paises, Provincias, Sedes, TiposDocumentos | Posible duplicado con personas1/personasegresadas |
| personas1 | | Igual que personas, sin MesesCobro | Personas, Alumnos, Areas, Ciudades, Estudios, Paises, Provincias, Sedes, TiposDocumentos | Aparente DUPLICADO de personas (sufijo "1") |
| personasegresadas | | Admin-generator sobre personas (egresadas) | Personas | Variante/duplicado de personas |
| planesdependientes | | CRUD de planes dependientes | PlanesDependientes | |
| planesestudios | | Gestión de planes de estudio | PlanesEstudios, Calendarios, Carreras, EstadosPlanes, FechasCalendario, Profesores | |
| planesobras | | CRUD de planes de obras (sociales) | PlanesObras | Relacionado a ObrasSociales (dominio médico) |
| profesionalesasociado | | CRUD de profesionales asociados | Profesionalesasociado | Término dominio médico |
| profesiones | | CRUD de profesiones | Profesiones | |
| profesores | | Gestión de profesores | Profesores, Personas, Ciudades, Estudios, Facultades, Paises, Provincias, TiposDocumentos | |
| provincias | | CRUD de provincias | Provincias | |
| reportes | | Reportes (mesas de examen) | MesasExamenes | |
| sedes | | CRUD de sedes | Sedes | |
| sfGuardAuth | | Login/logout del plugin sfGuardPlugin | sfGuardUser | Infraestructura de autenticación (plugin) |
| sfGuardUser | | Admin-generator de usuarios (plugin) | sfGuardUser | Infraestructura auth |
| sistemas | | Admin-generator sobre tabla sistemas | Sistemas | |
| solicitudes | | CRUD de solicitudes | Solicitudes | |
| solicitudeslibredeuda | | Solicitudes de libre deuda (responder) | SolicitudesLibredeuda, sfGuardUser | |
| tiposareas | | CRUD tipos de área | TiposAreas | |
| tiposasignaciones | | CRUD tipos de asignación | TiposAsignaciones | Modelo sin tabla en el dump |
| tiposaulas | | CRUD tipos de aula | TiposAulas | |
| tiposcargos | | CRUD tipos de cargo | TiposCargos | |
| tiposcarreras | | Admin-generator tipos de carrera | TiposCarreras | |
| tiposclases | | CRUD tipos de clase | TiposClases | |
| tiposcorrelatividades | | CRUD tipos de correlatividad | TiposCorrelatividades | |
| tiposdesignaciones | | Admin-generator tipos de designación | TiposDesignaciones | |
| tiposdesignacionesmesas | | CRUD tipos de designación en mesas | TiposDesignacionesMesas | |
| tiposdocumentacion | | CRUD tipos de documentación | TiposDocumentacion | |
| tiposdocumentos | | CRUD tipos de documento | TiposDocumentos | |
| tiposexamenes | | CRUD tipos de examen | TiposExamenes | |
| tiposfechascalendario | | CRUD tipos de fecha de calendario | TiposFechasCalendario | |
| tiposmaterias | | CRUD tipos de materia | TiposMaterias | |
| tipossedes | | CRUD tipos de sede | TiposSedes | |
| tipostitulos | | Admin-generator tipos de título | TiposTitulos | |
| titulos | | CRUD de títulos | Titulos | Modelo sin tabla en el dump |
| titulosplanes | | Títulos por plan de estudios | TitulosPlanes, PlanesEstudios | |
| transicionesmaterias | | Transiciones de materias entre planes | TransicionesMaterias, TransicionesPlanes | Modelos sin tabla en el dump |
| transicionesplanes | | Transiciones entre planes de estudio | TransicionesPlanes | Modelo sin tabla en el dump |

---

## B. Tablas del esquema (233) y módulos de alumnos que las referencian

La columna **"Módulos alumnos que la referencian"** se calcula de forma mecánica:
para cada tabla se listan los módulos de la Sección A cuya lista de Tablas/Modelos incluye
el modelo Doctrine que mapea a esa tabla (conversión CamelCase → snake_case). Celda vacía =
ningún módulo de `alumnos` la referencia (señal de posible tabla huérfana respecto de esta app).
Nota: algunas tablas figuran como "sin modelo" (`—`) en el esquema pero igualmente son
referenciadas por módulos (p. ej. `empleados_sede`, `sf_guard_user`, `profesionalesasociado`);
se conserva esa señal cruzada.

> El esquema (`referencia/schema.sql`) contiene 233 `CREATE TABLE`, todas listadas en esta sección.

| Tabla | ¿Se usa? | Modelo Doctrine | Qué representa | Módulos alumnos que la referencian | Observaciones |
|---|---|---|---|---|---|
| 2013alumnos | | — | Copia de alumnos 2013 | | LEGACY año "2013", sin modelo |
| 2013movacademicos | | — | Copia movimientos académicos 2013 | | LEGACY año "2013", sin modelo |
| activity_log | | — | Registro de actividad de usuarios | | OTRO DOMINIO (CRM), sin modelo |
| activosactivos | | — | Contenido incierto | | Nombre anómalo, sin modelo, probable scratch |
| alu96 | | — | Copia de alumnos "96" | | LEGACY "96", sin modelo |
| alumat196 | | — | Copia alumno-materia "196" | | LEGACY "196", sin modelo |
| alumatcrr | | — | Copia alumno-materia (crr) | | LEGACY sufijo "crr", sin modelo |
| alumnos | | Alumnos | Estudiantes | alumat, alumnos, api, aspirante, autogestion, bajas, cicloslectivos, derivaciones, egresados, encuestasalumnos, equivalencias, estadosalumno, examenes, expedientes, impresiones, informes, inscripciones, mesasexamenes, personas, personas1, personasegresadas | Núcleo académico |
| AlumnosViejo | | — | Copia vieja de alumnos | | LEGACY "Viejo" + case duplicado, sin modelo |
| alumnosviejo | | — | Copia vieja de alumnos | | LEGACY "viejo", duplicado, sin modelo |
| alu_mat | | AluMat | Relación alumno-materia | alumat, api, cicloslectivos, estadosalumno, impresiones, inscripciones, mesasexamenes | Núcleo académico |
| araucano_equivalencias | | — | Export equivalencias Araucano | | ARAUCANO (estadística nacional), sin modelo |
| araucano_estadisticas | | — | Export estadísticas Araucano | | ARAUCANO, sin modelo |
| araucano_examenes_rendidos | | — | Export exámenes rendidos Araucano | | ARAUCANO, sin modelo |
| araucano_extranjeros | | — | Export alumnos extranjeros Araucano | | ARAUCANO, sin modelo |
| areas | | Areas | Áreas administrativas/académicas | alumnos, calendarios, derivaciones, ingreso, mesasexamenes, personas, personas1 | |
| areas_cargos | | AreasCargos | Relación área-cargo | areascargos | |
| areas_carrera | | AreasCarrera | Relación área-carrera | areascarrera, carreras, derivaciones, egresados, estadisticas, graficos | |
| area_documentos | | AreaDocumentos | Categoría de documentos institucionales | areadocumentos | |
| asignaciones | | Asignaciones | Asignación docente a cátedra/materia | asignaciones | |
| asignaciones_clases | | AsignacionesClases | Asignación de clases | asignacionesclases, impresiones, inscripciones | |
| asignaciones_mesas | | AsignacionesMesas | Asignación de mesas de examen | | Modelo definido pero sin módulo que lo referencie |
| auditoria | | Auditoria | Log de auditoría interno | auditoria | |
| aulas | | Aulas | Aulas físicas | aulas | |
| autoridades | | Autoridades | Autoridades de la institución | autoridades, informes | |
| bajas_alumnos | | BajasAlumnos | Bajas de alumnos | bajas, estadosalumno | |
| calendarios | | Calendarios | Calendarios académicos | api, calendarios, informes, planesestudios | |
| cargo_autoridades | | CargoAutoridades | Cargos de autoridades | cargoautoridades | |
| carreras | | Carreras | Carreras | carreras, carrerassede, impresiones, informes, planesestudios | Núcleo académico |
| carreras_sede | | CarrerasSede | Relación carrera-sede | alumat, api, aspirante, inscripciones, materiasplanes | |
| catedras | | Catedras | Cátedras | api, asignacionesclases, catedras, cicloslectivos, comisiones, impresiones, inscripciones, materiasplanes, mesasexamenes | |
| catedras45 | | — | Copia de cátedras "45" | | LEGACY "45", sin modelo |
| categorias_titulos | | CategoriasTitulos | Categorías de títulos | categoriastitulos | |
| categoria_designaciones | | CategoriaDesignaciones | Categorías de designaciones | | |
| centros | | Centros | Centros | centros | |
| ciclos_lectivos | | CiclosLectivos | Ciclos lectivos | alumnos, api, aspirante, asignacionesclases, cicloslectivos, egresados, informes, inscripciones | |
| ciudades | | Ciudades | Ciudades | alumnos, aspirante, ciudades, expedientes, informes, personas, personas1, profesores | |
| clases | | Clases | Clases | asignacionesclases, clases | |
| clients | | — | Clientes (name/email/address) | | OTRO DOMINIO (CRM inglés), sin modelo |
| client_invoice | | — | Facturas de cliente | | OTRO DOMINIO (CRM/facturación), sin modelo |
| comments | | — | Comentarios genéricos | | OTRO DOMINIO (CRM inglés), sin modelo |
| condiciones | | Condiciones | Condiciones académicas | expedientes | |
| condiciones_expedientes | | CondicionesExpedientes | Condiciones de expedientes | expedientes | |
| condiciones_mesas | | CondicionesMesas | Condiciones de mesas de examen | condicionesmesas | |
| contactos | | Contactos | Contactos de personas | contactos, informes | |
| control | | — | Scratch de control de legajos | | Sin modelo, temporal |
| corregir | | — | Scratch de corrección materia-plan | | Nombre imperativo, sin modelo, temporal |
| correlatividades | | Correlatividades | Correlatividades entre materias | api, correlatividades, informes, inscripciones | |
| correlatividadesback | | — | Backup de correlatividades | | LEGACY sufijo "back", sin modelo |
| crr | | — | Copia legacy (crr) | | LEGACY nombre críptico, sin modelo |
| dedicaciones | | Dedicaciones | Dedicaciones docentes | | |
| departamentos | | Departamentos | Departamentos académicos | | |
| departments | | — | Departamentos (inglés) | | OTRO DOMINIO (CRM inglés), sin modelo |
| department_user | | — | Pivote departamento-usuario | | OTRO DOMINIO (CRM inglés), sin modelo |
| dependencias | | Dependencias | Dependencias | dependencias | |
| designaciones | | Designaciones | Designaciones docentes | designaciones, estadisticas | |
| designaciones_empleados | | DesignacionesEmpleados | Designaciones de empleados | alumnos, designacionesempleados, estadosalumno, informes | |
| designaciones_mesas | | DesignacionesMesas | Designaciones en mesas | designacionesmesas | |
| detalle_horarios | | DetalleHorarios | Detalle de horarios | detallehorarios | |
| detalle_nota | | DetalleNota | Detalle de notas | detallenota | |
| dias | | Dias | Días (catálogo) | | |
| divisiones | | — | Divisiones | | Sin modelo; timestamps blame pero no mapeado |
| dm_talk_message | | — | Mensajes de chat | | OTRO DOMINIO (chat/plugin), sin modelo |
| dm_talk_room | | — | Salas de chat | | OTRO DOMINIO (chat/plugin), sin modelo |
| dm_talk_speaker | | — | Participantes de chat | | OTRO DOMINIO (chat/plugin), sin modelo |
| documentacion | | Documentacion | Documentación | documentacion, documentacionplanes, expedientes | |
| documentacion_alumnos | | DocumentacionAlumnos | Documentación de alumnos | alumnos, api, aspirante, impresiones, inscripciones | |
| documentacion_expedientes | | DocumentacionExpedientes | Documentación de expedientes | expedientes | |
| documentacion_planes_estudios | | DocumentacionPlanesEstudios | Documentación de planes de estudio | alumnos, aspirante, documentacionplanes | |
| documentos_institucion | | DocumentosInstitucion | Documentos de la institución | documentosinstitucion | |
| documents | | — | Documentos (inglés) | | OTRO DOMINIO (CRM inglés), sin modelo |
| doc_laboral | | DocLaboral | Documentación laboral | doclaboral | |
| edificios | | Edificios | Edificios | aulas, edificios | |
| empleados | | Empleados | Empleados | designacionesempleados, empleados, estadisticas | |
| empleados_sede | | — | Pivote empleado-sede | alumnos, api, empleadossede, expedientes | Sin modelo (relación del dominio, no mapeada); referenciado vía modelo EmpleadosSede |
| encuestas | | Encuestas | Encuestas | api, encuestas, inscripciones | |
| encuestas_alumnos | | EncuestasAlumnos | Respuestas de encuestas de alumnos | encuestasalumnos | |
| equivalencias_alumnos | | EquivalenciasAlumnos | Equivalencias de alumnos | equivalencias | |
| escalas_notas | | EscalasNotas | Escalas de notas | escalasnotas | |
| estados_alumno | | EstadosAlumno | Estados de alumno | estadosalumno, informes | |
| estados_alumno_historial | | EstadosAlumnoHistorial | Historial de estados de alumno | estadosalumno | |
| estados_carreras | | EstadosCarreras | Estados de carreras | estadoscarreras | |
| estados_comisiones | | EstadosComisiones | Estados de comisiones | estadoscomisiones | |
| estados_designaciones | | EstadosDesignaciones | Estados de designaciones | | |
| estados_fechas_examenes | | — | Estados de fechas de examen | | Sin modelo (lookup del dominio, no mapeado) |
| estados_fechas_examenes_historial | | — | Historial estados fechas examen | | Sin modelo, no mapeado |
| estados_materia | | EstadosMateria | Estados de materia | estadosmateria, impresiones | |
| estados_mesas_examenes | | EstadosMesasExamenes | Estados de mesas de examen | mesasexamenes | |
| estados_mesas_examenes_historial | | EstadosMesasExamenesHistorial | Historial estados mesas | | |
| estados_planes | | EstadosPlanes | Estados de planes | estadosplanes, planesestudios | |
| estados_solicitudes | | EstadosSolicitudes | Estados de solicitudes | | |
| estado_civil | | EstadoCivil | Estado civil (catálogo) | | |
| estudios | | Estudios | Estudios de personas | alumnos, aspirante, estudios, expedientes, personas, personas1, profesores | |
| examenes | | Examenes | Exámenes | api, autogestion, examenes, inscripciones, mesasexamenes | |
| expedientes_derivaciones | | ExpedientesDerivaciones | Derivaciones de expedientes | derivaciones, expedientes | |
| expedientes_egresados | | ExpedientesEgresados | Expedientes de egresados | derivaciones, egresados, expedientes | |
| facultades | | Facultades | Facultades | alumnos, aspirante, bajas, egresados, estadisticas, facultades, profesores | |
| fechas_calendario | | FechasCalendario | Fechas de calendario | api, calendarios, fechascalendario, llamadosturno, periodosciclos, periodoscursadas, planesestudios | |
| ficha_alumnos | | FichaAlumnos | Ficha de alumnos | | Modelo definido dos veces en schema.yml |
| ficha_carga | | FichaCarga | Ficha de carga | | |
| grupomenu | | Grupomenu | Grupos de menú | grupomenu | |
| historialpaciente | | Historialpaciente | Historia clínica de paciente | historialpaciente, paciente | DOMINIO MÉDICO (tiene modelo) |
| historico_consultas | | HistoricoConsultas | Histórico de consultas | libredeuda | |
| horarios | | Horarios | Horarios | horarios, informes | |
| industries | | — | Rubros/industrias | | OTRO DOMINIO (CRM inglés), sin modelo |
| inscripciones_ciclo_lectivo | | InscripcionesCicloLectivo | Inscripciones al ciclo lectivo | api, informes | |
| inscripciones_mesas | | InscripcionesMesas | Inscripciones a mesas | autogestion, inscripcionesmesas | |
| integrations | | — | Integraciones externas | | OTRO DOMINIO (CRM inglés), sin modelo |
| invoices | | — | Facturas | | OTRO DOMINIO (CRM/facturación), sin modelo |
| invoice_task_time | | — | Facturación por tiempo de tareas | | OTRO DOMINIO (CRM/facturación), sin modelo |
| la196 | | — | Copia legacy plan "196" | | LEGACY "196", sin modelo |
| leads | | — | Prospectos (title/fk_client_id) | | OTRO DOMINIO (CRM inglés), sin modelo |
| legg | | — | Scratch de legajos | | Sin modelo, temporal |
| libredeuda_tramites | | LibredeudaTramites | Trámites de libre deuda | | |
| libros_actas | | LibrosActas | Libros de actas | equivalencias, librosactas, mesasexamenes | |
| lista_horarios | | ListaHorarios | Listas de horarios | listahorarios | |
| llamados_turno | | LlamadosTurno | Llamados/turnos de examen | fechascalendario, llamadosturno, mesasexamenes | |
| log_eventos_designaciones | | LogEventosDesignaciones | Log de eventos de designaciones | | |
| materias | | Materias | Materias | materias | Núcleo académico |
| materias_bajas | | MateriasBajas | Bajas de materias | | |
| materias_genericas | | MateriasGenericas | Materias genéricas | materiasgenericas | |
| materias_planes | | MateriasPlanes | Materias por plan de estudios | alumnos, api, catedras, cicloslectivos, comisiones, equivalencias, informes, inscripciones, materiasplanes, mesasexamenes | |
| memoria_equivalencias | | — | Snapshot de equivalencias | | LEGACY prefijo "memoria" (respaldo), sin modelo |
| memoria_estadisticas | | — | Snapshot de estadísticas | | LEGACY "memoria", sin modelo |
| memoria_examenes_rendidos | | — | Snapshot de exámenes rendidos | | LEGACY "memoria", sin modelo |
| menu | | Menu | Menú de la aplicación | menu | |
| mesas_examenes | | MesasExamenes | Mesas de examen | alumnos, api, autogestion, cicloslectivos, fechascalendario, impresiones, inscripciones, llamadosturno, mesasexamenes, reportes | |
| meses | | Meses | Meses (catálogo) | | |
| meses_cobro | | MesesCobro | Meses de cobro | personas | |
| migrations | | — | Registro de migraciones | | FRAMEWORK (estilo Laravel del CRM), sin modelo |
| migration_version | | — | Versión de migración | | FRAMEWORK (Doctrine/DBAL Migrations), sin modelo |
| modalidades_carreras | | ModalidadesCarreras | Modalidades de carreras | modalidadescarreras | |
| modos_egreso | | ModosEgreso | Modos de egreso | modosegreso | |
| modos_evaluaciones | | ModosEvaluaciones | Modos de evaluación | modosevaluaciones | |
| modos_inscripcion_cursadas | | ModosInscripcionCursadas | Modos de inscripción a cursadas | | |
| motivos | | Motivos | Motivos (catálogo) | | |
| motivos_bajas | | MotivosBajas | Motivos de baja | | |
| movimientos_alumnos | | MovimientosAlumnos | Movimientos académicos de alumnos | movimientosalumnos | |
| mp | | — | Copia de materias_planes | | LEGACY abreviatura críptica, sin modelo |
| niveles_estudios | | NivelesEstudios | Niveles de estudios | nivelesestudios | |
| nota | | — | Nota de lead (fk_user_id/fk_lead_id) | | OTRO DOMINIO (CRM), sin modelo |
| notes | | — | Notas (inglés) | | OTRO DOMINIO (CRM inglés), sin modelo |
| noticias | | Noticias | Noticias | noticias | |
| noticias_carrera | | NoticiasCarrera | Noticias por carrera | noticias | |
| notifications | | — | Notificaciones | | OTRO DOMINIO (CRM inglés), sin modelo |
| notification_categories | | — | Categorías de notificación | | OTRO DOMINIO (CRM inglés), sin modelo |
| no_docentes | | NoDocentes | Personal no docente | | |
| obras_sociales | | ObrasSociales | Obras sociales (salud) | informes, obrassociales | DOMINIO MÉDICO/salud (tiene modelo) |
| otra | | — | Scratch con columnas A/B/C | | Basura/temporal, sin modelo |
| p100 | | — | Copia de plan "100" | | LEGACY plan numerado, sin modelo |
| p101 | | — | Copia de plan "101" | | LEGACY plan numerado, sin modelo |
| p102 | | — | Copia de plan "102" | | LEGACY plan numerado, sin modelo |
| p95 | | — | Copia de plan "95" | | LEGACY plan numerado, sin modelo |
| p96 | | — | Copia de plan "96" | | LEGACY plan numerado, sin modelo |
| p99 | | — | Copia de plan "99" | | LEGACY plan numerado, sin modelo |
| paciente | | Paciente | Pacientes | paciente | DOMINIO MÉDICO (tiene modelo) |
| paises | | Paises | Países | alumnos, aspirante, expedientes, paises, personas, personas1, profesores | |
| password_resets | | — | Reseteo de contraseñas | | OTRO DOMINIO (auth Laravel/CRM), sin modelo |
| periodos_ciclos | | PeriodosCiclos | Períodos de ciclos | periodosciclos | |
| periodos_cursadas | | PeriodosCursadas | Períodos de cursada | api, periodoscursadas | |
| permissions | | — | Permisos | | OTRO DOMINIO (auth Laravel/CRM inglés), sin modelo |
| permission_role | | — | Pivote permiso-rol | | OTRO DOMINIO (auth Laravel/CRM), sin modelo |
| personas | | Personas | Personas físicas | alumnos, api, aspirante, egresados, empleados, horarios, informes, personas, personas1, personasegresadas, profesores | Núcleo del sistema |
| personas-original | | — | Copia "original" de personas | | LEGACY sufijo "-original", sin modelo |
| personas1 | | — | Copia de personas | | LEGACY sufijo "1", sin modelo |
| personasaluv | | — | Copia personas (alumnos v) | | LEGACY sufijo críptico "aluv", sin modelo |
| pesborrar | | — | Tabla marcada para borrar | | LEGACY nombre "borrar", sin modelo |
| pivoteconomicas | | — | Scratch materias-plan económicas | | Sin modelo, temporal/pivote |
| planes_dependientes | | PlanesDependientes | Planes dependientes | planesdependientes | |
| planes_estudios | | PlanesEstudios | Planes de estudio | aspirante, bajas, catedras, comisiones, correlatividades, documentacionplanes, egresados, estadisticas, estadosalumno, expedientes, facultades, impresiones, informes, inscripciones, materiasplanes, mesasexamenes, planesestudios, titulosplanes | Núcleo académico |
| planes_obras | | PlanesObras | Planes de obra social | planesobras | DOMINIO salud/obras sociales (tiene modelo) |
| prof | | — | Copia legacy de profesores | | LEGACY nombre trunco, sin modelo |
| prof2 | | — | Copia legacy de profesores | | LEGACY sufijo "2", sin modelo |
| prof2013 | | — | Copia de profesores 2013 | | LEGACY año "2013", sin modelo |
| profesionalesasociado | | — | Profesionales asociados | profesionalesasociado | DOMINIO MÉDICO/otro, sin modelo; referenciado vía modelo Profesionalesasociado |
| profesiones | | Profesiones | Profesiones (catálogo) | profesiones | |
| profesores | | Profesores | Profesores | planesestudios, profesores | |
| profile | | — | Perfil de usuario (liga sf_guard_user) | | Sin modelo; puente auth, señal de app secundaria |
| profmauricio | | — | Copia personal "mauricio" | | LEGACY nombre propio de dev, sin modelo |
| provincias | | Provincias | Provincias | alumnos, aspirante, expedientes, personas, personas1, profesores, provincias | |
| recibos_generados | | RecibosGenerados | Recibos generados | | |
| resoluciones_profesores | | ResolucionesProfesores | Resoluciones de profesores | | |
| roles | | — | Roles | | OTRO DOMINIO (auth Laravel/CRM inglés), sin modelo |
| role_user | | — | Pivote rol-usuario | | OTRO DOMINIO (auth Laravel/CRM), sin modelo |
| rosario | | — | Copia legacy (nombre propio) | | LEGACY nombre ciudad/persona, sin modelo |
| rosarioborrar | | — | Copia marcada para borrar | | LEGACY "borrar", sin modelo |
| sectores | | Sectores | Sectores | | |
| sedes | | Sedes | Sedes | alumnos, bajas, catedras, comisiones, edificios, egresados, estadisticas, impresiones, informes, personas, personas1, sedes | |
| settings | | — | Configuración de la app CRM | | OTRO DOMINIO (CRM), sin modelo |
| sexo | | Sexo | Sexo (catálogo) | | |
| sf_guard_forgot_password | | — | Recuperación de contraseña sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_guard_group | | — | Grupos de usuarios sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_guard_group_permission | | — | Permisos de grupo sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_guard_permission | | — | Permisos sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_guard_remember_key | | — | Tokens "recordarme" sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_guard_user | | — | Usuarios sfGuard (auth real del sistema) | alumnos, api, aspirante, derivaciones, estadosalumno, informes, sfGuardAuth, sfGuardUser, solicitudeslibredeuda | FRAMEWORK (sfGuardPlugin), sin modelo; auth real, NO borrar |
| sf_guard_user_group | | — | Pivote usuario-grupo sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_guard_user_permission | | — | Pivote usuario-permiso sfGuard | | FRAMEWORK (sfGuardPlugin), sin modelo |
| sf_korero_channel | | — | Canales de chat Korero | | FRAMEWORK (plugin chat), sin modelo |
| sf_korero_message | | — | Mensajes de chat Korero | | FRAMEWORK (plugin chat), sin modelo |
| sf_who_is_online_user | | — | Usuarios en línea | | FRAMEWORK (plugin presencia), sin modelo |
| sistemas | | Sistemas | Sistemas/módulos | sistemas | |
| solicitudes | | Solicitudes | Solicitudes | api, solicitudes | |
| solicitudes_libredeuda | | SolicitudesLibredeuda | Solicitudes de libre deuda | solicitudeslibredeuda | |
| tasks | | — | Tareas (title/fk_client_id/deadline) | | OTRO DOMINIO (CRM inglés), sin modelo |
| tasks_time | | — | Tiempos de tareas | | OTRO DOMINIO (CRM inglés), sin modelo |
| test | | — | Tabla de prueba | | Basura/temporal, sin modelo |
| tipos_areas | | TiposAreas | Tipos de áreas | tiposareas | |
| tipos_aulas | | TiposAulas | Tipos de aulas | tiposaulas | |
| tipos_cargos | | TiposCargos | Tipos de cargos | tiposcargos | |
| tipos_carreras | | TiposCarreras | Tipos de carreras | estadisticas, tiposcarreras | |
| tipos_clases | | TiposClases | Tipos de clases | tiposclases | |
| tipos_correlatividades | | TiposCorrelatividades | Tipos de correlatividades | tiposcorrelatividades | |
| tipos_cursadas | | TiposCursadas | Tipos de cursada | | |
| tipos_designaciones | | TiposDesignaciones | Tipos de designaciones | tiposdesignaciones | |
| tipos_designaciones_mesas | | TiposDesignacionesMesas | Tipos de designaciones en mesas | mesasexamenes, tiposdesignacionesmesas | |
| tipos_documentacion | | TiposDocumentacion | Tipos de documentación | tiposdocumentacion | |
| tipos_documentos | | TiposDocumentos | Tipos de documentos | alumnos, aspirante, egresados, personas, personas1, profesores, tiposdocumentos | |
| tipos_examenes | | TiposExamenes | Tipos de exámenes | tiposexamenes | |
| tipos_fechas_calendario | | TiposFechasCalendario | Tipos de fechas de calendario | tiposfechascalendario | |
| tipos_inscriptos | | TiposInscriptos | Tipos de inscriptos | | |
| tipos_materias | | TiposMaterias | Tipos de materias | tiposmaterias | |
| tipos_planes | | TiposPlanes | Tipos de planes | | |
| tipos_resoluciones | | TiposResoluciones | Tipos de resoluciones | | |
| tipos_sedes | | TiposSedes | Tipos de sedes | tipossedes | |
| tipos_titulos | | TiposTitulos | Tipos de títulos | tipostitulos | |
| titulos_planes | | TitulosPlanes | Títulos por plan | titulosplanes | |
| tmpbajas | | — | Temporal de bajas | | LEGACY prefijo "tmp", sin modelo |
| tmpmp | | — | Temporal de materias_planes | | LEGACY prefijo "tmp", sin modelo |
| tmp_pacientes | | — | Temporal de pacientes | | LEGACY "tmp" + DOMINIO MÉDICO, sin modelo |
| unidades_de_tiempo | | UnidadesDeTiempo | Unidades de tiempo (catálogo) | | |
| users | | — | Usuarios (CRM) | | OTRO DOMINIO (auth Laravel/CRM inglés), sin modelo |

---

## C. Candidatas a huérfanas / de otro dominio

Agrupación por señal objetiva. No implica decisión de baja: son candidatas a revisar.

### Copias/versiones legacy (año, número, "viejo/original/borrar")

`2013alumnos`, `2013movacademicos`, `alu96`, `alumat196`, `alumatcrr`, `AlumnosViejo`,
`alumnosviejo`, `catedras45`, `correlatividadesback`, `crr`, `la196`, `mp`, `p95`, `p96`,
`p99`, `p100`, `p101`, `p102`, `personas-original`, `personas1`, `personasaluv`, `prof`,
`prof2`, `prof2013`, `profmauricio`, `rosario`, `rosarioborrar`, `pesborrar`

### Snapshots "memoria_"

`memoria_equivalencias`, `memoria_estadisticas`, `memoria_examenes_rendidos`

### Exportación estadística nacional (Araucano)

`araucano_equivalencias`, `araucano_estadisticas`, `araucano_examenes_rendidos`,
`araucano_extranjeros`

### Scratch/temporales/basura

`activosactivos`, `control`, `corregir`, `otra`, `legg`, `pivoteconomicas`, `test`,
`tmpbajas`, `tmpmp`, `tmp_pacientes`

### Otro dominio — app CRM/leads en inglés (estilo Laravel)

`activity_log`, `clients`, `client_invoice`, `comments`, `departments`, `department_user`,
`documents`, `industries`, `integrations`, `invoices`, `invoice_task_time`, `leads`, `nota`,
`notes`, `notifications`, `notification_categories`, `password_resets`, `permissions`,
`permission_role`, `profile`, `roles`, `role_user`, `settings`, `tasks`, `tasks_time`,
`users`, `migrations`, `migration_version`

### Otro dominio — médico/clínica

`paciente`, `historialpaciente`, `tmp_pacientes`, `profesionalesasociado`, y con matiz de
salud `obras_sociales`, `planes_obras`. Estas dos últimas **sí** tienen modelo Doctrine y
están referenciadas (`obras_sociales` por `informes`/`obrassociales`) — revisar antes de
descartar.

### Framework/plugins Symfony (infraestructura, no negocio)

`sf_guard_*` (auth real del sistema, **NO borrar**), `sf_korero_channel`, `sf_korero_message`,
`sf_who_is_online_user`, `dm_talk_message`, `dm_talk_room`, `dm_talk_speaker`

### Módulos alumnos de dominio médico (espejo de lo anterior)

`paciente`, `historialpaciente`, `obrassociales`, `planesobras`, `profesionalesasociado`

### Módulos alumnos duplicados/sospechosos

`personas` vs `personas1` vs `personasegresadas` (tres variantes sobre el mismo modelo
Personas); `aspirante` solapa fuertemente con `alumnos`; `diplomas` (stub, 1 acción);
`detalleplan`, `titulos`, `tiposasignaciones`, `transicionesmaterias`, `transicionesplanes`
(modelos sin tabla en el dump).

---

> **Nota — modelos en `schema.yml` SIN tabla en el dump:** `Titulos`, `TransicionesPlanes`,
> `TransicionesMaterias`, `CondicionesMesasCatedras`, `EstadosEquivalencias`,
> `MateriasEquivalencias`. Son modelos huérfanos (definidos en el esquema pero sin tabla
> materializada); revisar.
