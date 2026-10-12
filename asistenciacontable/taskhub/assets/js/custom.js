"use strict";

$("#modal-add-fonts").fireModal({
  size: 'modal-lg',
  title: 'Add New Fonts',
  body: $("#modal-add-font-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'formBtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-leave-editors").fireModal({
  size: 'modal-lg',
  title: 'Editor solicitud de permiso',
  body: $("#modal-leave-editors-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          //   location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Ok',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'formBtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-edit-leave").fireModal({
  size: 'modal-lg',
  title: 'Actualizar Permiso',
  body: $("#modal-edit-leave-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'formBtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-leave").fireModal({
  size: 'modal-lg',
  title: 'Solicitud de permiso',
  body: $("#modal-add-leave-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'formBtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-language").fireModal({
  size: 'modal-lg',
  title: 'Agregar Idioma',
  body: $("#modal-add-language-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});


$("#modal-search-msg").fireModal({
  size: 'modal-lg',
  title: 'Buscar',
  body: $("#modal-search-msg-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: true,

  onFormSubmit: function (modal, e, form) {

    let form_data = $(e.target).serialize();
    e.preventDefault();

  },
  shown: function (modal, form) {
  },

});


$(".modal-edit-workspace").fireModal({
  title: 'Editar espacio trabajo',
  body: $("#modal-edit-Workspace-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });

    e.preventDefault();

  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'workspacebtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-workspace").fireModal({
  title: 'Crear espacio trabajo',
  body: $("#modal-add-Workspace-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data

    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        if (result['error'] == false) {
          location.reload();
        } else {
          $('#fire-modal-21').on('hidden.bs.modal', function (e) {
            $(this).find('form').trigger('reset');
          });
          csrfName = result['csrfName'];
          csrfHash = result['csrfHash'];
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();

        }
      }
    });

    e.preventDefault();

  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'workspacebtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-info-group").fireModal({
  size: 'modal-lg',
  title: 'Información Grupo',
  body: $("#modal-info-group-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
});

$("#modal-edit-group").fireModal({
  size: 'modal-lg',
  title: 'Editar Grupo',
  body: $("#modal-edit-group-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Borrar grupo',
      submit: false,
      class: 'btn btn-danger text-white',
      id: 'delete_group',

    },
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-group").fireModal({
  size: 'modal-lg',
  title: 'Crear Grupo',
  body: $("#modal-add-group-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-add-task-details").fireModal({
  size: 'modal-lg',
  title: '',
  body: $("#modal-add-task-details-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });
    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Enviar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-task").fireModal({
  size: 'modal-lg',
  title: 'Editar Tarea',
  body: $("#modal-edit-task-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });
    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-task").fireModal({
  size: 'modal-lg',
  title: 'Agregar Tarea',
  body: $("#modal-add-task-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-milestone").fireModal({
  size: 'modal-lg',
  title: 'Editar hito',
  body: $("#modal-edit-milestone-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-milestone").fireModal({
  size: 'modal-lg',
  title: 'Agregar hito',
  body: $("#modal-add-milestone-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-project").fireModal({
  size: 'modal-lg',
  title: 'Editar Proyecto',
  body: $("#modal-edit-project-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-report").fireModal({
  size: 'modal-lg',
  title: 'Editar Reporte',
  body: $("#modal-edit-report-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-archieve").fireModal({
  size: 'modal-lg',
  title: 'Editar Archivo',
  body: $("#modal-edit-archieve-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    // console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-project").fireModal({
  size: 'modal-lg',
  title: 'Agregar Proyecto',
  body: $("#modal-add-project-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-report").fireModal({
  size: 'modal-lg',
  title: 'Agregar Reporte',
  body: $("#modal-add-report-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-archieve").fireModal({
  size: 'modal-lg',
  title: 'Agregar Archivo',
  body: $("#modal-add-archieve-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-note").fireModal({
  title: 'Editar Nota',
  body: $("#modal-edit-note-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Borrar Nota',
      submit: false,
      class: 'btn btn-danger text-white delete-note-alert',
      id: 'delete_note',

    },
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-note").fireModal({
  title: 'Agregar Nota',
  body: $("#modal-add-note-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addnotebtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-user").fireModal({
  size: 'modal-lg',
  title: 'Agregar Usuario',
  body: $("#modal-add-user-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'adduserbtn',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-user").fireModal({
  size: 'modal-lg',
  title: 'Editar Usuario',
  body: $("#modal-edit-user-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'updateuserbtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-event").fireModal({

  title: 'Agregar Evento',
  body: $("#modal-add-event-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addeventbtn',
      handler: function (modal) {
      }
    }
  ]

});

$(".modal-edit-event").fireModal({
  title: 'Editar Evento',
  body: $("#modal-edit-event-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'editeventbtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-announcement").fireModal({
  size: 'modal-lg',
  title: 'Agregar Anuncio',
  body: $("#modal-add-announcement-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,

  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.alert-message').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Crear',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-announcement").fireModal({
  title: 'Editar Anuncio',
  size: 'modal-lg',
  body: $("#modal-edit-announcement-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.alert-message').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'editannouncementtbtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-expense").fireModal({

  size: 'modal-lg',
  title: 'Agregar Gasto',
  body: $("#modal-add-expense-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addexpensebtn',
      handler: function (modal) {
      }
    }
  ]

});

$(".modal-edit-expense").fireModal({
  size: 'modal-lg',
  title: 'Editar Gasto',
  body: $("#modal-edit-expense-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Grabar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'editexpensebtn',
      handler: function (modal) {
      }
    }
  ]

});



$(".modal-edit-expense-type").fireModal({
  title: 'Editar tipo gasto',
  body: $("#modal-edit-expense-type-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'updateexpensetypebtn',
      handler: function (modal) {
      }
    }
  ]
});
$("#modal-add-expense-type").fireModal({

  title: 'Agregar tipo gasto',
  body: $("#modal-add-expense-type-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    e.preventDefault();
    // Form Data
    var formData = new FormData(this);
    var is_reload = formData.get("is_reload");
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          if (is_reload == 1) {
            location.reload();
          } else {
            $('#expense_type_id').find('option').remove().end();
            $.ajax({
              type: 'POST',
              url: base_url + 'expenses/get_expense_types',
              data: csrfName + '=' + csrfHash,
              dataType: "json",
              // quietMillis: 500,
              success: function (data) {
                csrfName = data.csrfName;
                csrfHash = data.csrfHash;
                delete data["csrfName"];
                delete data["csrfHash"];
                var $newOption = $("<option selected></option>").val('').text('Elija...')
                $("#expense_type_id").append($newOption).trigger('change');
                $.each(data, function (i) {
                  $.each(data[i], function (key, val) {

                    var $newOption = $("<option></option>").val(val.id).text(val.title)
                    $("#expense_type_id").append($newOption).trigger('change');
                  });
                });
                $('#expense_type_id').val(result['expense_type_id']).trigger('change');


              }

            });
            $('#update_expense_type_id').find('option').remove().end();
            $.ajax({
              type: 'POST',
              url: base_url + 'expenses/get_expense_types',
              data: csrfName + '=' + csrfHash,
              dataType: "json",
              // quietMillis: 500,
              success: function (data) {
                csrfName = data.csrfName;
                csrfHash = data.csrfHash;
                delete data["csrfName"];
                delete data["csrfHash"];
                var $newOption = $("<option selected></option>").val('').text('Elija...')
                $("#update_expense_type_id").append($newOption).trigger('change');
                $.each(data, function (i) {
                  $.each(data[i], function (key, val) {

                    var $newOption = $("<option></option>").val(val.id).text(val.title)
                    $("#update_expense_type_id").append($newOption).trigger('change');
                  });
                });
                $('#update_expense_type_id').val(result['expense_type_id']).trigger('change');


              }

            });
            form.stopProgress();
            $('#fire-modal-4').modal('hide');
            $('#fire-modal-5').modal('hide');
            $('#fire-modal-51').modal('hide');
            // $('#modal-add-expense').trigger('click');
            iziToast.success({
              title: result['message'],
              message: '',
              position: 'topRight'
            });
          }

        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();

        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addexpensetypebtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-item").fireModal({
  size: 'modal-md',
  title: 'Agregar Item',
  body: $("#modal-add-item-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    // let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    var is_reload = formData.get("is_reload");
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          if (is_reload == 1) {
            location.reload();
          } else {
            $('#item_id').find('option').remove().end();
            $.ajax({
              type: 'POST',
              url: base_url + 'items/get_items',
              data: csrfName + '=' + csrfHash,
              dataType: "json",
              // quietMillis: 500,
              success: function (data) {
                csrfName = data["csrfName"];
                csrfHash = data["csrfHash"];
                delete data["csrfName"];
                delete data["csrfHash"];
                var $newOption = $("<option selected></option>").val('').text('Elija...')

                $("#item_id").append($newOption).trigger('change');
                $.each(data, function (i) {
                  $.each(data[i], function (key, val) {

                    var $newOption = $("<option></option>").val(val.id).text(val.title)

                    $("#item_id").append($newOption).trigger('change');
                  });
                });
                $('#item_id').val(result['item_id']).trigger('change');


              }

            });

            form.stopProgress();
            $('#fire-modal-31').modal('hide');
            $('#fire-modal-3').modal('hide');
            iziToast.success({
              title: result['message'],
              message: '',
              position: 'topRight'
            });
          }


        } else {
          form.stopProgress();
          // $('#fire-modal-4').modal('hide');
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Enviar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'additembtn',
      handler: function (modal) {
      }
    }
  ]

});

$(".modal-edit-item").fireModal({
  size: 'modal-md',
  title: 'Editar Item',
  body: $("#modal-edit-item-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    // let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          $('#item_id').find('option').remove().end();
          $.ajax({
            type: 'POST',
            url: base_url + 'items/get_items',
            data: csrfName + '=' + csrfHash,
            dataType: "json",
            // quietMillis: 500,
            success: function (data) {
              csrfName = data["csrfName"];
              csrfHash = data["csrfHash"];
              delete data["csrfName"];
              delete data["csrfHash"];
              var $newOption = $("<option selected></option>").val('').text('Elija...')

              $("#item_id").append($newOption).trigger('change');
              $.each(data, function (i) {
                $.each(data[i], function (key, val) {

                  var $newOption = $("<option></option>").val(val.id).text(val.title)

                  $("#item_id").append($newOption).trigger('change');
                });
              });
              $('#item_id').val(result['item_id']).trigger('change');


            }

          });

          form.stopProgress();
          $('#fire-modal-41').modal('hide');
          $('#fire-modal-4').modal('hide');
          iziToast.success({
            title: result['message'],
            message: '',
            position: 'topRight'
          });

        } else {
          form.stopProgress();
          // $('#fire-modal-4').modal('hide');
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Enviar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'edititembtn',
      handler: function (modal) {
      }
    }
  ]

});

$("#modal-add-tax").fireModal({
  size: 'modal-md',
  title: 'Agregar Impuesto',
  body: $("#modal-add-tax-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addtaxbtn',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-tax").fireModal({
  size: 'modal-md',
  title: 'Editar Impuesto',
  body: $("#modal-edit-tax-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'updatetaxbtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-unit").fireModal({
  size: 'modal-md',
  title: 'Agregar Unidad',
  body: $("#modal-add-unit-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addunitbtn',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-unit").fireModal({
  size: 'modal-md',
  title: 'Editar Unidad',
  body: $("#modal-edit-unit-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);

    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];

        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    //   console.log(form)
  },
  buttons: [
    {
      text: 'Actualizar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'updateunitbtn',
      handler: function (modal) {
      }
    }
  ]
});

$("#modal-add-payment").fireModal({

  size: 'modal-lg',
  title: 'Agregar Pago',
  body: $("#modal-add-payment-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addpaymentbtn',
      handler: function (modal) {
      }
    }
  ]

});
$(".modal-edit-payment-mode").fireModal({

  size: 'modal-md',
  title: 'Editar Modo de Pago',
  body: $("#modal-edit-payment-mode-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Grabar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'editpaymentmodebtn',
      handler: function (modal) {
      }
    }
  ]

});

$("#modal-add-payment-mode").fireModal({

  title: 'Agregar Modo de Pago',
  body: $("#modal-add-payment-mode-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
  onFormSubmit: function (modal, e, form) {
    // Form Data
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          $('#payment_mode_id').find('option').remove().end();
          $.ajax({
            type: 'POST',
            url: base_url + 'payments/get_payment_modes',
            data: csrfName + '=' + csrfHash,
            dataType: "json",
            // quietMillis: 500,
            success: function (data) {
              csrfName = data.csrfName;
              csrfHash = data.csrfHash;
              delete data["csrfName"];
              delete data["csrfHash"];
              var $newOption = $("<option selected></option>").val('').text('Elija...')
              $("#payment_mode_id").append($newOption).trigger('change');
              $.each(data, function (i) {
                $.each(data[i], function (key, val) {

                  var $newOption = $("<option></option>").val(val.id).text(val.title)
                  $("#payment_mode_id").append($newOption).trigger('change');
                });
              });
              $('#payment_mode_id').val(result['payment_mode_id']).trigger('change');


            }

          });
          form.stopProgress();
          $('#fire-modal-41').modal('hide');
          $('#fire-modal-4').modal('hide');
          iziToast.success({
            title: result['message'],
            message: '',
            position: 'topRight'
          });
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();

        }
      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {
    console.log(form)
  },
  buttons: [
    {
      text: 'Agregar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'addpaymentmodebtn2',
      handler: function (modal) {
      }
    }
  ]
});

$(".modal-edit-payment").fireModal({
  size: 'modal-lg',
  title: 'Editar Pago',
  body: $("#modal-edit-payment-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,


  onFormSubmit: function (modal, e, form) {
    // Form Data
    let form_data = $(e.target).serialize();

    var formData = new FormData(this);

    formData.append(csrfName, csrfHash);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {

        csrfName = result['csrfName'];
        csrfHash = result['csrfHash'];
        if (result['error'] == false) {
          form.stopProgress();
          location.reload();
        } else {
          form.stopProgress();
          modal.find('.modal-body').prepend('<div class="alert alert-danger">' + result['message'] + '</div>')
          modal.find('.alert-danger').delay(4000).fadeOut();
        }

      }
    });

    e.preventDefault();
  },
  shown: function (modal, form) {

    //   console.log(form)
  },

  buttons: [
    {
      text: 'Grabar',
      submit: true,
      class: 'btn btn-primary btn-shadow',
      id: 'editpaymentbtn',
      handler: function (modal) {
      }
    }
  ]

});

$(".modal-workspace-users").fireModal({
  size: 'modal-lg',
  title: 'Usuarios de Espacio Trabajo',
  body: $("#modal-workspace-users-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
});
$(".modal-workspace-clients").fireModal({
  size: 'modal-lg',
  title: 'Workspace Clients',
  body: $("#modal-workspace-clients-part"),
  footerClass: 'bg-whitesmoke',
  autoFocus: false,
});
