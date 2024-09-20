<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');


$config = array(

    'tipo_anexo_put' => array(
        array('field' => 'tipo_anexo', 'label' => 'Nombre tipo anexo', 'rules' => 'trim|required|min_length[5]|max_length[150]'),
        array('field' => 'observaciones_tipo_anexo', 'label' => 'Descripción tipo anexo', 'rules' => 'trim|min_length[5]|max_length[200]'),
    ),
    'tipo_anexo_post' => array(
        array('field' => 'ide_tipo_anexo', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'tipo_anexo', 'label' => 'Nombre tipo anexo', 'rules' => 'trim|required|min_length[5]|max_length[25]'),
        array('field' => 'observaciones_tipo_anexo', 'label' => 'Descripción tipo anexo', 'rules' => 'trim|min_length[5]|max_length[200]'),
    ),
    'tipo_documento_put' => array(
        array('field' => 'tipo_documental', 'label' => 'tipo documental', 'rules' => 'trim|required|min_length[5]|max_length[150]'),
        array('field' => 'descripcion_tipo_documental', 'label' => 'Descripción documental', 'rules' => 'trim|min_length[5]|max_length[200]'),
        array('field' => 'dias_tramite', 'label' => 'Días tramite', 'rules' => 'trim|numeric'),
        array('field' => 'tipo_documento', 'label' => 'Tipo documental', 'rules' => 'trim'),
    ),

    'tipo_documento_post' => array(
        array('field' => 'ide_tipo_documental', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'tipo_documental', 'label' => 'tipo documental', 'rules' => 'trim|required|min_length[5]|max_length[150]'),
        array('field' => 'descripcion_tipo_documental', 'label' => 'Descripción documental', 'rules' => 'trim|min_length[5]|max_length[200]'),
        array('field' => 'dias_tramite', 'label' => 'Días tramite', 'rules' => 'trim|numeric'),
        array('field' => 'tipo_documento', 'label' => 'Tipo documental', 'rules' => 'trim'),

    ),
    'terceros_put' => array(
        array('field' => 'nit_tercero', 'label' => 'Nit. tercero', 'rules' => 'trim'),
        array('field' => 'ide_tipo_identf', 'label' => 'tipo identificación', 'rules' => 'trim|required|numeric'),
        array('field' => 'tipo_tercero', 'label' => 'tipo tercero', 'rules' => 'trim'),
        array('field' => 'nombres_tercero', 'label' => 'Nombres terceros', 'rules' => 'trim'),
        array('field' => 'apellidos_tercero', 'label' => 'apellidos terceros', 'rules' => 'trim'),
        array('field' => 'tel_fijo_tercero', 'label' => 'telefono', 'rules' => 'trim|min_length[8]'),
        array('field' => 'cel_fijo_tercero', 'label' => 'celular', 'rules' => 'trim|min_length[10]|max_length[10]'),
        array('field' => 'direccion_tercero', 'label' => 'dirección', 'rules' => 'trim|min_length[3]'),
        array('field' => 'nom_entidad', 'label' => 'Nombre de entidad', 'rules' => 'trim|min_length[2]'),
    ),
    'terceros_post' => array(
        array('field' => 'ide_tercero', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'ide_tipo_identf', 'label' => 'tipo identificación', 'rules' => 'trim|required'),
        array('field' => 'tipo_tercero', 'label' => 'tipo tercero', 'rules' => 'trim'),
        array('field' => 'nit_tercero', 'label' => 'Nit. tercero', 'rules' => 'trim'),
        array('field' => 'nombres_tercero', 'label' => 'Nombres terceros', 'rules' => 'trim'),
        array('field' => 'apellidos_tercero', 'label' => 'apellidos terceros', 'rules' => 'trim'),
        array('field' => 'tel_fijo_tercero', 'label' => 'telefono terceros', 'rules' => 'trim|min_length[6]'),
        array('field' => 'cel_fijo_tercero', 'label' => 'celular terceros', 'rules' => 'trim|min_length[10]|max_length[30]'),
        array('field' => 'direccion_tercero', 'label' => 'dirección terceros', 'rules' => 'trim|min_length[3]'),
    ),
    'series_carpetas_put' => array(
        array('field' => 'nombre_expediente', 'label' => 'Nombre de expediente', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'numero_expediente', 'label' => 'numero_expediente', 'rules' => 'trim|numeric'),
        array('field' => 'nom_usuario', 'label' => 'Nombre usuario', 'rules' => 'trim|numeric'),
    ),
    'series_carpetas_post' => array(
        array('field' => 'ide_expediente', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'nombre_expediente', 'label' => 'Nombre de expediente', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'numero_expediente', 'label' => 'numero_expediente', 'rules' => 'trim|numeric'),
        array('field' => 'nom_usuario', 'label' => 'Nombre usuario', 'rules' => 'trim|numeric'),
    ),
    'series_doctal_put' => array(
        array('field' => 'nombre_serie', 'label' => 'Nombre de serie', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'cod_dependencia', 'label' => 'Código de la dependencia', 'rules' => 'trim|numeric'),
        array('field' => 'registro_padre', 'label' => 'Registro padre', 'rules' => 'trim|numeric'),
    ),
    'series_doctal_post' => array(
        array('field' => 'ide_serie', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'nombre_serie', 'label' => 'Nombre de serie', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'cod_dependencia', 'label' => 'Código de la dependencia', 'rules' => 'trim|numeric'),
        array('field' => 'registro_padre', 'label' => 'Registro padre', 'rules' => 'trim|numeric'),
    ),
    'tipo_exp_put' => array(
        array('field' => 'nombre_tipo', 'label' => 'Nombre de expedient', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'descripcion_tipo', 'label' => 'Descipcion de expedient', 'rules' => 'trim|min_length[5]|max_length[150]'),
    ),

    'tipo_exp_post' => array(
        array('field' => 'id_tipo_documento', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'nombre_tipo', 'label' => 'Nombre de expedient', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'descripcion_tipo', 'label' => 'Descipcion de expedient', 'rules' => 'trim|min_length[5]|max_length[150]'),
    ),
    'roles_put' => array(
        array('field' => 'nombre_rol', 'label' => 'Nombre de rol', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'descripcion_rol', 'label' => 'Descipcion de rol', 'rules' => 'trim|min_length[5]|max_length[150]'),
    ),
    'roles_post' => array(
        array('field' => 'ide_rol', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'nombre_rol', 'label' => 'Nombre de rol', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'descripcion_rol', 'label' => 'Descipcion de rol', 'rules' => 'trim|min_length[5]|max_length[150]'),
    ), // se agrego el put de natural doc ya que la pedia en el controlador pero no exisitia y no me dejaba agregar
    'natural_doc_put' => array(
        array('field' => 'medio_recepcion', 'label' => 'Medio de recepcion', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'descripcion_medio', 'label' => 'Descipcion de recepcion', 'rules' => 'trim|min_length[5]|max_length[150]'),
    ),
    'natural_doc_post' => array(
        array('field' => 'ide_medio_recepcion', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'medio_recepcion', 'label' => 'Medio de recepcion', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'descripcion_medio', 'label' => 'Descipcion de recepcion', 'rules' => 'trim|min_length[5]|max_length[150]'),

    ),
    'gestion_put' => array(
        array('field' => 'ide_tipo_radicado', 'label' => 'Tipo de radicado', 'rules' => 'trim|required|numeric'),
        array('field' => 'ide_tipo_documental', 'label' => 'Tipo documento', 'rules' => 'required|numeric'),
        array('field' => 'ide_medio_recepcion', 'label' => 'Naturaleza del documento', 'rules' => 'required|numeric'),
        array('field' => 'medio_recepcion', 'label' => 'Medio de recepccion', 'rules' => 'trim|min_length[3]'),
        array('field' => 'fecha_unidad_documental', 'label' => 'Fecha unidad doumentak', 'rules' => 'trim|min_length[10]'),
        array('field' => 'numero_documento_radicado', 'label' => 'Nro documento', 'rules' => 'trim|min_length[1]|max_length[50]'),
        array('field' => 'ide_unidad_adtiva_destino', 'label' => 'Oficina destino', 'rules' => 'numeric'),
        array('field' => 'ide_funcionario_destino', 'label' => 'Funcionario destino', 'rules' => 'numeric'),
        array('field' => 'ide_unidad_adtiva_origen', 'label' => 'Funcionario destino', 'rules' => 'numeric'),
        array('field' => 'ide_funcionario_origen', 'label' => 'Funcionario destino', 'rules' => 'numeric'),
        array('field' => 'observaciones_unidad_documental', 'label' => 'Observaciones', 'rules' => 'trim|min_length[10]|max_length[2500]'),
        array('field' => 'ide_anexo_a', 'label' => 'Codigo del anexo', 'rules' => 'trim|numeric'),
        array('field' => 'nom_usuario', 'label' => 'Usuario registra', 'rules' => 'trim|required'),
        array('field' => 'fecha_registro', 'label' => 'Fecha hora registro', 'rules' => 'trim|required'),
        array('field' => 'activo_radicado', 'label' => 'Estado radicado', 'rules' => 'trim|required'),
        array('field' => 'nro_anexos', 'label' => 'Nro de anexos', 'rules' => 'numeric'),

    ),
    'rad_entrada_put' => array(
        //array('field' => 'ide_radicado', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'ide_tipo_radicado', 'label' => 'Tipo de radicado', 'rules' => 'trim|required|numeric'),
        array('field' => 'ide_tipo_documental', 'label' => 'Tipo documento', 'rules' => 'required|numeric'),
        array('field' => 'ide_medio_recepcion', 'label' => 'Naturaleza del documento', 'rules' => 'required|numeric'),
        array('field' => 'fecha_documento_radicado', 'label' => 'Fecha documento', 'rules' => 'trim|min_length[10]'),
        array('field' => 'numero_documento_radicado', 'label' => 'Nro documento', 'rules' => 'trim|min_length[1]|max_length[50]'),
        array('field' => 'ide_radicado_respuesta', 'label' => 'Radicado respuesta', 'rules' => 'numeric'),
        array('field' => 'fecha_radicado', 'label' => 'Fecha de radicacion', 'rules' => 'trim|min_length[10]|max_length[19]'),
        array('field' => 'asunto_documento', 'label' => 'Asunto', 'rules' => 'trim|min_length[10]|max_length[2500]'),
        array('field' => 'numero_folios', 'label' => 'Nro folios', 'rules' => 'trim|numeric'),
        array('field' => 'fecha_respuesta', 'label' => 'Fecha limite respuesta', 'rules' => 'trim|min_length[10]'),
        array('field' => 'ide_tercero_origen', 'label' => 'Persona/entidad Origen', 'rules' => 'required|numeric'),
        array('field' => 'persona_elaboro', 'label' => 'Persona elaboró', 'rules' => 'trim|min_length[3]|max_length[200]'),

        array('field' => 'ide_unidad_administrativa_destino', 'label' => 'Oficina destino', 'rules' => 'numeric'),
        array('field' => 'ide_funcionario_destino', 'label' => 'Funcionario destino', 'rules' => 'numeric'),

        array('field' => 'ide_unidad_administrativa_origen', 'label' => 'Funcionario destino', 'rules' => 'numeric'),
        array('field' => 'ide_funcionario_origen', 'label' => 'Funcionario destino', 'rules' => 'numeric'),

        array('field' => 'observaciones_radicado', 'label' => 'Observaciones', 'rules' => 'trim|min_length[10]|max_length[2500]'),
        array('field' => 'ide_anexo_a', 'label' => 'Codigo del anexo', 'rules' => 'trim|numeric'),
        array('field' => 'nom_usuario', 'label' => 'Usuario registra', 'rules' => 'trim|required'),
        array('field' => 'fecha_registro', 'label' => 'Fecha hora registro', 'rules' => 'trim|required'),
        array('field' => 'activo_radicado', 'label' => 'Estado radicado', 'rules' => 'trim|required'),
        array('field' => 'nro_anexos', 'label' => 'Nro de anexos', 'rules' => 'numeric'),
    ),
    'rad_anulado_post' => array(
        array('field' => 'ide_radicado', 'label' => 'Ide del radicado', 'rules' => 'trim|required|numeric'),
        array('field' => 'fecha_anulado', 'label' => 'Fecha anulacion', 'rules' => 'required'),
        array('field' => 'motivo_anulado', 'label' => 'Motivo anulacion', 'rules' => 'required'),
        array('field' => 'cod_user', 'label' => 'Codigo usuario', 'rules' => 'required|numeric'),
        array('field' => 'fecha_registro', 'label' => 'Fecha registro', 'rules' => 'required'),
    ),
    'usuarios_put' => array(
        array('field' => 'documento', 'label' => 'Número de documento', 'rules' => 'trim|min_length[5]'),
        array('field' => 'nombres', 'label' => 'Nombres usuarios', 'rules' => 'trim|min_length[2]'),
        array('field' => 'apellidos', 'label' => 'Apellidos usuarios', 'rules' => 'trim|min_length[2]'),
        array('field' => 'login', 'label' => 'Nombre de pqrd', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'pass', 'label' => 'Contraseña', 'rules' => 'trim|required|min_length[5]'),
        array('field' => 'pass2', 'label' => 'Confoirmación de Contraseña', 'rules' => 'trim|required|min_length[5]|matches[pass2]'),
        array('field' => 'mail_user', 'label' => 'correo Electrónico', 'rules' => 'trim|min_length[5]|valid_email'),
        array('field' => 'ide_rol', 'label' => 'Rol', 'rules' => 'trim|numeric'),

    ),
    'usuarios_post' => array(
        array('field' => 'cod_user', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'nombres', 'label' => 'Nombres usuarios', 'rules' => 'trim|min_length[2]'),
        array('field' => 'apellidos', 'label' => 'Apellidos usuarios', 'rules' => 'trim|min_length[2]'),
        array('field' => 'login', 'label' => 'Nombre de pqrd', 'rules' => 'trim|required|min_length[2]'),
        array('field' => 'mail_user', 'label' => 'correo Electrónico', 'rules' => 'trim|min_length[5]|valid_email'),
        array('field' => 'ide_rol', 'label' => 'Rol', 'rules' => 'trim|numeric'),
    ),
    'changePassUser_post' => array(
        array('field' => 'cod_user', 'label' => 'Codigo', 'rules' => 'trim|required|numeric'),
        array('field' => 'pass', 'label' => 'Contraseña', 'rules' => 'trim|required|min_length[5]'),
        array('field' => 'pass2', 'label' => 'Confoirmación de Contraseña', 'rules' => 'trim|required|min_length[5]|matches[pass2]'),
    ),
    'seguimiento_put' => array(
        array('field' => 'ide_unidad_doctal', 'label' => 'Radicado', 'rules' => 'trim|required|numeric'),
        array('field' => 'text_gestion', 'label' => 'Descripción/Obserbación', 'rules' => 'trim|required|min_length[15]'),
        array('field' => 'feccha_gestion', 'label' => 'Fecha en que se realiza la gestion', 'rules' => 'trim|required'),
        array('field' => 'ide_usuario', 'label' => 'Codigo del pqrd autenticado', 'rules' => 'trim|required'),
        array('field' => 'nom_usuario', 'label' => 'Usuario autenticado', 'rules' => 'trim|required|min_length[5]'),
        array('field' => 'ide_anexo', 'label' => 'Código del anexo', 'rules' => 'trim|numeric'),
        array('field' => 'ide_estado', 'label' => 'Estado', 'rules' => 'trim|numeric|required'),
    )


);
