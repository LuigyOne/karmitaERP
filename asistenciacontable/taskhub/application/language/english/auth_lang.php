<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Auth Lang - English
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Author: Daniel Davis
*         @ourmaninjapan
*
* Location: https://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  03.09.2013
*
* Description:  English language file for Ion Auth example views
*
*/

// Errors
$lang['error_csrf'] = 'This form post did not pass our security checks.';

// Login
$lang['login_heading']         = 'Inicio de Sesión';
$lang['login_subheading']      = 'Por favor iniciar sesión con su email/usuario y contraseña a continuación.';
$lang['login_identity_label']  = 'Email/Usuario:';
$lang['login_password_label']  = 'Contraseña:';
$lang['login_remember_label']  = 'Recordarme:';
$lang['login_submit_btn']      = 'Iniciar Sesión';
$lang['login_forgot_password'] = '¿Olvidó su contraseña?';

// Index
$lang['index_heading']           = 'Usuarios';
$lang['index_subheading']        = 'A continuación se muestra una lista de los usuarios.';
$lang['index_fname_th']          = 'Nombres';
$lang['index_lname_th']          = 'Apellidos';
$lang['index_email_th']          = 'Email';
$lang['index_groups_th']         = 'Grupos';
$lang['index_status_th']         = 'Status';
$lang['index_action_th']         = 'Acción';
$lang['index_active_link']       = 'Activo';
$lang['index_inactive_link']     = 'Inactivo';
$lang['index_create_user_link']  = 'Crear a nuevo usario';
$lang['index_create_group_link'] = 'Crear a nuevo grupo';

// Deactivate User
$lang['deactivate_heading']                  = 'Deactivar Usuario';
$lang['deactivate_subheading']               = 'Esta usted seguro de querer desactivar al usuario \'%s\'';
$lang['deactivate_confirm_y_label']          = 'Si:';
$lang['deactivate_confirm_n_label']          = 'No:';
$lang['deactivate_submit_btn']               = 'Enviar';
$lang['deactivate_validation_confirm_label'] = 'confirmación';
$lang['deactivate_validation_user_id_label'] = 'Usuario ID';

// Create User
$lang['create_user_heading']                           = 'Crear Usuario';
$lang['create_user_subheading']                        = 'Por favor ingrese la información del usuario\'s a continuación.';
$lang['create_user_fname_label']                       = 'Nombres:';
$lang['create_user_lname_label']                       = 'Apellidos:';
$lang['create_user_company_label']                     = 'Empresa:';
$lang['create_user_identity_label']                    = 'Identificación:';
$lang['create_user_email_label']                       = 'Email:';
$lang['create_user_phone_label']                       = 'Celular:';
$lang['create_user_password_label']                    = 'Contraseña:';
$lang['create_user_password_confirm_label']            = 'Confirmar Contraseña:';
$lang['create_user_submit_btn']                        = 'Crear usuario';
$lang['create_user_validation_fname_label']            = 'Nombres';
$lang['create_user_validation_lname_label']            = 'Apellidos';
$lang['create_user_validation_identity_label']         = 'Identificación';
$lang['create_user_validation_email_label']            = 'Email';
$lang['create_user_validation_phone_label']            = 'Celular';
$lang['create_user_validation_company_label']          = 'Empresa';
$lang['create_user_validation_password_label']         = 'Contraseña';
$lang['create_user_validation_password_confirm_label'] = 'Confirmar contraseña';
$lang['create_user_validation_address_label'] = 'Dirección';
$lang['create_user_validation_city_label'] = 'Ciudad';
$lang['create_user_validation_state_label'] = 'Provincia';
$lang['create_user_validation_zip_code_label'] = 'Codigo Postal';
$lang['create_user_validation_country_label'] = 'País';

// Edit User
$lang['edit_user_heading']                           = 'Editar usuario';
$lang['edit_user_subheading']                        = 'Por favor ingrese la información del usuario\'s a continuación.';
$lang['edit_user_fname_label']                       = 'Nombres:';
$lang['edit_user_lname_label']                       = 'Apellidos:';
$lang['edit_user_company_label']                     = 'Empresa:';
$lang['edit_user_email_label']                       = 'Email:';
$lang['edit_user_phone_label']                       = 'Celular:';
$lang['edit_user_password_label']                    = 'Contraseña: (si va a cambiar la contraseña)';
$lang['edit_user_password_confirm_label']            = 'Confirmar contraseña: (si cambio la contraseña)';
$lang['edit_user_groups_heading']                    = 'Miembro de los grupos';
$lang['edit_user_submit_btn']                        = 'Guardas usuario';
$lang['edit_user_validation_fname_label']            = 'Nombres';
$lang['edit_user_validation_lname_label']            = 'Apellidos';
$lang['edit_user_validation_email_label']            = 'Email';
$lang['edit_user_validation_phone_label']            = 'Celular';
$lang['edit_user_validation_company_label']          = 'Empresa';
$lang['edit_user_validation_groups_label']           = 'Grupos';
$lang['edit_user_validation_password_label']         = 'Contraseña';
$lang['edit_user_validation_password_confirm_label'] = 'Confirmar contraseña';
$lang['edit_user_validation_address_label'] = 'Dirección';
$lang['edit_user_validation_city_label'] = 'Ciudad';
$lang['edit_user_validation_state_label'] = 'Provincia';
$lang['edit_user_validation_zip_code_label'] = 'Codigo Postal';
$lang['edit_user_validation_country_label'] = 'País';

// Create Group
$lang['create_group_title']                  = 'Crear grupo';
$lang['create_group_heading']                = 'Crear grupo';
$lang['create_group_subheading']             = 'Por favor ingrese la información del grupo a continuación.';
$lang['create_group_name_label']             = 'Nombre del grupo:';
$lang['create_group_desc_label']             = 'Descripción:';
$lang['create_group_submit_btn']             = 'Crear grupo';
$lang['create_group_validation_name_label']  = 'Nombre del grupo';
$lang['create_group_validation_desc_label']  = 'Descripción';

// Edit Group
$lang['edit_group_title']                  = 'Editar grupo';
$lang['edit_group_saved']                  = 'Guardas grupo';
$lang['edit_group_heading']                = 'Editar groupo';
$lang['edit_group_subheading']             = 'Por favor ingrese la información del grupo a continuación.';
$lang['edit_group_name_label']             = 'Nombre del grupo:';
$lang['edit_group_desc_label']             = 'Descripción:';
$lang['edit_group_submit_btn']             = 'Guardar grupo';
$lang['edit_group_validation_name_label']  = 'Nombre de grupo';
$lang['edit_group_validation_desc_label']  = 'Descripción';

// Change Password
$lang['change_password_heading']                               = 'Cambiar contraseña';
$lang['change_password_old_password_label']                    = 'Contraseña anterior:';
$lang['change_password_new_password_label']                    = 'Nueva contraseña (debe tener al menos %s caracteres):';
$lang['change_password_new_password_confirm_label']            = 'Confirmar la nueva contraseña:';
$lang['change_password_submit_btn']                            = 'Cambiar';
$lang['change_password_validation_old_password_label']         = 'Contraseña anterior';
$lang['change_password_validation_new_password_label']         = 'Nueva contraseña';
$lang['change_password_validation_new_password_confirm_label'] = 'Confirmar nueva contraseña';

// Forgot Password
$lang['forgot_password_heading']                 = 'Olvidaste la contraseña';
$lang['forgot_password_subheading']              = 'Por favor ingrese su %s entonces nosotros podremos enviar a su email un link para reiniar su contraseña.';
$lang['forgot_password_email_label']             = '%s:';
$lang['forgot_password_submit_btn']              = 'Enviar';
$lang['forgot_password_validation_email_label']  = 'Dirección de email';
$lang['forgot_password_identity_label'] = 'Identificación';
$lang['forgot_password_email_identity_label']    = 'Email';
$lang['forgot_password_email_not_found']         = 'No registramos la dirección de email proporcionada.';
$lang['forgot_password_identity_not_found']      = 'No registramos el nombre de usuario proporcionado.';

// Reset Password
$lang['reset_password_heading']                               = 'Cambiar contraseña';
$lang['reset_password_new_password_label']                    = 'Nueva contraseña (debe tener al menos %s caracteres):';
$lang['reset_password_new_password_confirm_label']            = 'Confirmar la nueva contraseña:';
$lang['reset_password_submit_btn']                            = 'Cambiar';
$lang['reset_password_validation_new_password_label']         = 'Nueva contraseña';
$lang['reset_password_validation_new_password_confirm_label'] = 'Confirmar la nueva contraseña';
