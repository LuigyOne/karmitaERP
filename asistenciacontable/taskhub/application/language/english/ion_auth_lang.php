<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Lang - English
*
* Author: Ben Edmunds
*         ben.edmunds@gmail.com
*         @benedmunds
*
* Location: https://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  03.14.2010
*
* Description:  English language file for Ion Auth messages and errors
*
*/

// Account Creation
$lang['account_creation_successful']            = 'Cuenta creada exitosamente';
$lang['account_creation_unsuccessful']          = 'No se puede crear una cuenta';
$lang['account_creation_duplicate_email']       = 'Correo electrónico ya utilizado o no válido';
$lang['account_creation_duplicate_identity']    = 'Identificación ya utilizada o inválida';
$lang['account_creation_missing_default_group'] = 'El grupo predeterminado no está configurado';
$lang['account_creation_invalid_default_group'] = 'Nombre de grupo predeterminado no válido';


// Password
$lang['password_change_successful']          = 'Contraseña cambiada exitosamente';
$lang['password_change_unsuccessful']        = 'No se puede cambiar la contraseña';
$lang['forgot_password_successful']          = 'Email de restablecimiento de contraseña enviado';
$lang['forgot_password_unsuccessful']        = 'No se puede enviar por email el link de reinicio de contraseña';

// Activation
$lang['activate_successful']                 = 'Cuenta activada';
$lang['activate_unsuccessful']               = 'No se puede activar la cuenta';
$lang['deactivate_successful']               = 'Cuenta desactivada';
$lang['deactivate_unsuccessful']             = 'No se puede desactivar la cuenta';
$lang['activation_email_successful']         = 'Email de activación enviado. Por favor, revise su bandeja de entrada o correo no deseado.';
$lang['activation_email_unsuccessful']       = 'No se puede enviar el email de activación';
$lang['deactivate_current_user_unsuccessful']= 'No puedes desactivarte tú mismo.';

// Login / Logout
$lang['login_successful']                    = 'Inició sesión exitosamente';
$lang['login_unsuccessful']                  = 'Inicio de sesión incorrecto';
$lang['login_unsuccessful_not_active']       = 'La cuenta está inactiva';
$lang['login_timeout']                       = 'Bloqueado temporalmente. Inténtalo de nuevo más tarde.';
$lang['logout_successful']                   = 'Se cerró sesión exitosamente';

// Account Changes
$lang['update_successful']                   = 'Información de la cuenta actualizada correctamente';
$lang['update_unsuccessful']                 = 'No se puede actualizar la información de la cuenta';
$lang['delete_successful']                   = 'Usuario eliminada';
$lang['delete_unsuccessful']                 = 'No se puede eliminar el usuario';

// Groups
$lang['group_creation_successful']           = 'Grupo creado exitosamente';
$lang['group_already_exists']                = 'El nombre del grupo ya está creado';
$lang['group_update_successful']             = 'Detalles del grupo actualizados';
$lang['group_delete_successful']             = 'Grupo eliminado';
$lang['group_delete_unsuccessful']           = 'No se puede eliminar el grupo';
$lang['group_delete_notallowed']             = 'No se puede eliminar el grupo de administradoras';
$lang['group_name_required']                 = 'El nombre del grupo es un campo obligatorio';
$lang['group_name_admin_not_alter']          = 'El nombre del grupo de administración no se puede cambiar';

// Activation Email
$lang['email_activation_subject']            = 'Activación de la cuenta';
$lang['email_activate_heading']              = 'Activar cuenta para %s';
$lang['email_activate_subheading']           = 'Por favor haga clic en este enlace para %s.';
$lang['email_activate_link']                 = 'Activa tu cuenta';

// Forgot Password Email
$lang['email_forgotten_password_subject']    = 'Verificación de olvido de contraseña';
$lang['email_forgot_password_heading']       = 'Restablecer contraseña para %s';
$lang['email_forgot_password_subheading']    = 'Por favor haga clic en este enlace para %s.';
$lang['email_forgot_password_link']          = 'Restablecer su contraseña';

