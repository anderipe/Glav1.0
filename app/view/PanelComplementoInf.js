/*
 * File: app/view/PanelComplementoInf.js
 *
 * This file was generated by Sencha Architect version 2.2.2.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.0.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('siadno.view.PanelComplementoInf', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.PanelComplementoInf',

    height: 600,
    width: 360,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    collapsed: false,
    iconCls: 'icon-application_view_list',
    title: 'Despacho',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    margins: '3 3 3 3',
                    height: 65,
                    id: 'formParametros',
                    maxHeight: 65,
                    layout: {
                        align: 'stretch',
                        type: 'hbox'
                    },
                    bodyPadding: 10,
                    url: 'clases/interfaces/InterfazRegistroServicios.php',
                    items: [
                        {
                            xtype: 'datefield',
                            flex: 10,
                            margins: '0 3 0 0',
                            fieldLabel: 'Fecha Inicial',
                            labelAlign: 'top',
                            name: 'fechainicial',
                            submitFormat: 'Y-m-d',
                            listeners: {
                                change: {
                                    fn: me.onDatefieldChange,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'datefield',
                            flex: 10,
                            margins: '0 3 0 0',
                            fieldLabel: 'Fecha Final',
                            labelAlign: 'top',
                            name: 'fechafinal',
                            submitFormat: 'Y-m-d',
                            listeners: {
                                change: {
                                    fn: me.onDatefieldChange1,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'combobox',
                            flex: 10,
                            fieldLabel: 'Estado Servicio',
                            labelAlign: 'top',
                            name: 'idestadoservicio',
                            value: 0,
                            editable: false,
                            autoSelect: false,
                            displayField: 'nombre',
                            queryMode: 'local',
                            queryParam: 'idestadoservicio',
                            valueField: 'idestadoservicio',
                            listeners: {
                                change: {
                                    fn: me.onComboboxChange,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            flex: 7,
                            margin: '0 0 0 3',
                            iconAlign: 'top',
                            iconCls: 'icon-page_go',
                            text: 'Recargar',
                            listeners: {
                                click: {
                                    fn: me.onButtonClick,
                                    scope: me
                                }
                            }
                        }
                    ]
                },
                {
                    xtype: 'gridpanel',
                    flex: 1,
                    margins: '3 3 3 3',
                    id: 'gridServicios',
                    autoScroll: true,
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            width: 50,
                            defaultWidth: 50,
                            dataIndex: 'idservicio',
                            text: 'Código'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 60,
                            defaultWidth: 60,
                            dataIndex: 'matricula',
                            text: 'Matricula'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 60,
                            defaultWidth: 60,
                            dataIndex: 'estado',
                            text: 'Estado'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 100,
                            dataIndex: 'empleado',
                            text: 'Encargado'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 100,
                            dataIndex: 'fecharegistro',
                            text: 'Entrada'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 100,
                            dataIndex: 'fechaentrega',
                            text: 'Salida'
                        }
                    ],
                    selModel: Ext.create('Ext.selection.RowModel', {
                        listeners: {
                            select: {
                                fn: me.onRowselectionmodelSelect,
                                scope: me
                            }
                        }
                    })
                },
                {
                    xtype: 'form',
                    flex: 1,
                    margins: '3 3 3 3',
                    id: 'formServicio',
                    maxHeight: 250,
                    minHeight: 250,
                    bodyPadding: 10,
                    title: 'Información del Servicio Seleccionado',
                    url: 'clases/interfaces/InterfazRegistroServicios.php',
                    items: [
                        {
                            xtype: 'hiddenfield',
                            anchor: '100%',
                            fieldLabel: 'Label',
                            name: 'idpersona'
                        },
                        {
                            xtype: 'hiddenfield',
                            anchor: '100%',
                            fieldLabel: 'Label',
                            name: 'idservicio'
                        },
                        {
                            xtype: 'fieldcontainer',
                            height: 27,
                            margin: 0,
                            padding: 0,
                            layout: {
                                align: 'stretch',
                                type: 'hbox'
                            },
                            fieldLabel: 'Identificación',
                            items: [
                                {
                                    xtype: 'combobox',
                                    flex: 1,
                                    margins: '0',
                                    margin: '0 0 0 0',
                                    padding: 0,
                                    labelPad: 0,
                                    name: 'idtipoidentificacion',
                                    value: 0,
                                    editable: false,
                                    displayField: 'abreviatura',
                                    queryMode: 'local',
                                    store: 'dumyStore',
                                    valueField: 'idtipoidentificacion',
                                    listeners: {
                                        select: {
                                            fn: me.onComboboxSelect111,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'combobox',
                                    flex: 1,
                                    margins: '0 0 0 2',
                                    padding: 0,
                                    labelPad: 0,
                                    name: 'identificacion',
                                    enableKeyEvents: true,
                                    hideTrigger: true,
                                    autoSelect: false,
                                    displayField: 'identificacion',
                                    minChars: 5,
                                    queryMode: 'local',
                                    queryParam: 'identificacion',
                                    valueField: 'identificacion',
                                    listeners: {
                                        change: {
                                            fn: me.onComboboxChange1,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'fieldcontainer',
                            height: 28,
                            layout: {
                                align: 'stretch',
                                type: 'hbox'
                            },
                            fieldLabel: 'Nombres',
                            items: [
                                {
                                    xtype: 'textfield',
                                    flex: 1,
                                    name: 'nombres'
                                },
                                {
                                    xtype: 'textfield',
                                    flex: 1,
                                    margins: '0 0 0 2',
                                    name: 'apellidos'
                                }
                            ]
                        },
                        {
                            xtype: 'fieldcontainer',
                            height: 28,
                            layout: {
                                align: 'stretch',
                                type: 'hbox'
                            },
                            fieldLabel: 'Fecha Nacimiento',
                            items: [
                                {
                                    xtype: 'datefield',
                                    flex: 1,
                                    autoShow: true,
                                    name: 'fechanacimiento',
                                    format: 'Y-m-d',
                                    submitFormat: 'Y-m-d'
                                },
                                {
                                    xtype: 'radiogroup',
                                    flex: 1,
                                    margins: '0 0 0 2',
                                    items: [
                                        {
                                            xtype: 'radiofield',
                                            name: 'sexo',
                                            boxLabel: 'M',
                                            checked: true,
                                            inputValue: '1'
                                        },
                                        {
                                            xtype: 'radiofield',
                                            name: 'sexo',
                                            boxLabel: 'F',
                                            inputValue: '2'
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'fieldcontainer',
                            height: 28,
                            layout: {
                                align: 'stretch',
                                type: 'hbox'
                            },
                            fieldLabel: 'Teléfono y EMail',
                            items: [
                                {
                                    xtype: 'textfield',
                                    flex: 1,
                                    name: 'telefonos'
                                },
                                {
                                    xtype: 'textfield',
                                    flex: 1,
                                    margins: '0 0 0 2',
                                    name: 'email'
                                }
                            ]
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            fieldLabel: 'Dirección',
                            name: 'direccion'
                        },
                        {
                            xtype: 'fieldcontainer',
                            height: 28,
                            layout: {
                                align: 'stretch',
                                type: 'hbox'
                            },
                            fieldLabel: 'Estado y Fecha',
                            items: [
                                {
                                    xtype: 'combobox',
                                    flex: 1,
                                    name: 'idestadoservicio',
                                    value: 0,
                                    editable: false,
                                    autoSelect: false,
                                    displayField: 'nombre',
                                    queryMode: 'local',
                                    valueField: 'idestadoservicio'
                                },
                                {
                                    xtype: 'datefield',
                                    flex: 1,
                                    margins: '0 0 0 2',
                                    name: 'fechaentrega',
                                    format: 'Y-m-d',
                                    startDay: 1,
                                    submitFormat: 'Y-m-d'
                                },
                                {
                                    xtype: 'displayfield',
                                    flex: 1,
                                    margins: '0 0 0 2',
                                    margin: '',
                                    name: 'horaentrega'
                                }
                            ]
                        },
                        {
                            xtype: 'combobox',
                            width: 330,
                            fieldLabel: 'Encargado',
                            name: 'idempleado',
                            value: 0,
                            editable: false,
                            autoSelect: false,
                            displayField: 'nombres',
                            queryMode: 'local',
                            store: 'dumyStore',
                            valueField: 'idempleado'
                        }
                    ]
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    flex: 1,
                    dock: 'bottom',
                    items: [
                        {
                            xtype: 'tbfill'
                        },
                        {
                            xtype: 'buttongroup',
                            columns: 2,
                            items: [
                                {
                                    xtype: 'button',
                                    iconCls: 'icon-page_save',
                                    text: 'Guardar Servicio',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick1,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                }
            ],
            listeners: {
                added: {
                    fn: me.onPanelAdded,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },

    onDatefieldChange: function(field, newValue, oldValue, eOpts) {
        this.traerServicios();
    },

    onDatefieldChange1: function(field, newValue, oldValue, eOpts) {
        this.traerServicios();
    },

    onComboboxChange: function(field, newValue, oldValue, eOpts) {
        this.traerServicios();
    },

    onButtonClick: function(button, e, eOpts) {
        this.traerServicios();
    },

    onRowselectionmodelSelect: function(rowmodel, record, index, eOpts) {
        this.registroSeleccionado=record;
        siadno.limpiarFormulario('PanelComplementoInf', null, null);

        this.idservicio=record.get('idservicio');
        this.idempleado=record.get('idempleado');
        this.idestadoservicio=record.get('idestadoservicio');
        this.fechaentrega=record.get('fechaentrega');
        this.horaentrega=record.get('horaentrega');
        this.idpersona=record.get('idpersona');

        //alert(record.get('horaentrega'));




        //alert(record.get('idempleado'));
        //alert(record.get('idservicio'));

        //this.formularioservicio.getForm().loadRecord(record);

        this.idPersona.setValue(record.get('idpersona'));
        this.fidempleado.setValue(record.get('idempleado'));
        this.idServicio.setValue(record.get('idservicio'));
        this.fidestadoservicio.setValue(record.get('idestadoservicio'));

        if(!Ext.isEmpty(record.get('fechaentrega'))){
            this.ffechaentrega.setValue(record.get('fechaentrega'));
            this.fhoraentrega.setValue(record.get('horaentrega'));
        }else{
            this.ffechaentrega.setValue(null);
            this.fhoraentrega.setValue(null);
        }


        if(Ext.isEmpty(record.get('identificacion'))){
            return;
        }

        this.idTipoIdentificacion.setValue(record.get('idtipoidentificacion'));
        this.identificacion.setValue(record.get('identificacion'));
    },

    onComboboxSelect111: function(combo, records, eOpts) {
        var combo=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=identificacion]')[0];
        this.traerPersona(records[0].get('idtipoidentificacion'), combo.getValue());
    },

    onComboboxChange1: function(field, newValue, oldValue, eOpts) {
        siadno.limpiarFormulario('PanelComplementoInf', null, ['idtipoidentificacion', 'identificacion']);
        var comboIdentificacion=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=identificacion]')[0];        
        this.traerPersona(this.comboTiposIdentificacion.getValue(), comboIdentificacion.getValue());
    },

    onButtonClick1: function(button, e, eOpts) {
        var callback=function(){
            this.gridServicios.getStore().load();
        };


        siadno.enviarFormulario.call(this, false, 'PanelComplementoInf form[id=formServicio]',{accion:115}, callback, null);
    },

    onPanelAdded: function(component, container, pos, eOpts) {
        var clase=Ext.ClassManager.get("siadno.store.registroservicios.tiposidentificacion");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.registroservicios.tiposidentificacion', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        autoLoad: true,
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:101},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazRegistroServicios.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idtipoidentificacion',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idtipoidentificacion'
                        },
                        {
                            name: 'nombre'
                        },
                        {
                            name: 'abreviatura'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.registroservicios.estadosservicio");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.registroservicios.estadosservicio', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:112},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazRegistroServicios.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idestadoservicio',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idestadoservicio'
                        },
                        {
                            name: 'nombre'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.registroservicios.servicios2");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.registroservicios.servicios2', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        pageSize: 7,
                        proxy: {
                            extraParams:{accion:113},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazRegistroServicios.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idservicio',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idservicio'
                        },
                        {
                            name: 'fecharegistro'
                        },
                        {
                            name: 'fechaentrega'
                        },
                        {
                            name: 'horaentrega'
                        },
                        {
                            name: 'matricula'
                        },
                        {
                            name: 'estado'
                        },
                        {
                            name: 'empleado'
                        },
                        {
                            name: 'idpersona'
                        },
                        {
                            name: 'idempleado'
                        },
                        {
                            name: 'idtipoidentificacion'
                        },
                        {
                            name: 'identificacion'
                        },
                        {
                            name: 'idestadoservicio'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.registroservicios.encargados");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.registroservicios.encargados', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        autoLoad: true,
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:104},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazRegistroServicios.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idempleado',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idempleado'
                        },
                        {
                            name: 'nombres'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }


        this.fidservicio=Ext.ComponentQuery.query('PanelComplementoInf field[name=idservicio]')[0];;
        this.fidempleado=Ext.ComponentQuery.query('PanelComplementoInf field[name=idempleado]')[0];;
        this.fidpersona=Ext.ComponentQuery.query('PanelComplementoInf field[name=idpersona]')[0];;
        this.fidestadoservicio=Ext.ComponentQuery.query('PanelComplementoInf field[name=idestadoservicio]')[1];
        this.ffechaentrega=Ext.ComponentQuery.query('PanelComplementoInf field[name=fechaentrega]')[0];;
        this.fhoraentrega=Ext.ComponentQuery.query('PanelComplementoInf field[name=horaentrega]')[0];;

        this.formularioservicio=Ext.ComponentQuery.query('PanelComplementoInf form[id=formServicio]')[0];
        this.idPersona=Ext.ComponentQuery.query('PanelComplementoInf hidden[name=idpersona]')[0];
        this.idServicio=Ext.ComponentQuery.query('PanelComplementoInf hidden[name=idservicio]')[0];
        this.idTipoIdentificacion=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=idtipoidentificacion]')[0];
        this.identificacion=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=identificacion]')[0];
        this.fechaEntrega=Ext.ComponentQuery.query('PanelComplementoInf datefield[name=fechaentrega]')[0];

        this.gridServicios=Ext.ComponentQuery.query('PanelComplementoInf grid[id=gridServicios]')[0];
        this.gridServicios.reconfigure(Ext.create('siadno.store.registroservicios.servicios2'));

        this.comboEstadosServicio=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=idestadoservicio]')[0];
        this.comboEstadosServicio.bindStore(Ext.create('siadno.store.registroservicios.estadosservicio'));
        this.comboEstadosServicio.getStore().load();

        this.comboEstadosServicio2=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=idestadoservicio]')[1];
        this.comboEstadosServicio2.bindStore(Ext.create('siadno.store.registroservicios.estadosservicio'));
        this.comboEstadosServicio2.getStore().load();

        this.comboTiposIdentificacion=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=idtipoidentificacion]')[0];
        this.comboTiposIdentificacion.bindStore(Ext.create('siadno.store.registroservicios.tiposidentificacion'));
        this.comboTiposIdentificacion.getStore().load();

        this.comboEmpleados=Ext.ComponentQuery.query('PanelComplementoInf combobox[name=idempleado]')[0];
        this.comboEmpleados.bindStore(Ext.create('siadno.store.registroservicios.encargados'));
        this.comboEmpleados.getStore().load();

        this.fechaInicial=Ext.ComponentQuery.query('PanelComplementoInf datefield[name=fechainicial]')[0];
        this.fechaFinal=Ext.ComponentQuery.query('PanelComplementoInf datefield[name=fechafinal]')[0];

        this.fechaInicial.suspendEvents(false);
        this.fechaFinal.suspendEvents(false);

        var dt = new Date();
        this.fechaInicial.setValue(dt);
        this.fechaFinal.setValue(dt);


        this.fechaInicial.resumeEvents();
        this.fechaFinal.resumeEvents();

        this.traerServicios();

        /*
        combo=Ext.ComponentQuery.query('PanelRegistroServicios combobox[name=idtipoidentificacion]')[0];
        combo.bindStore(Ext.create('siadno.store.registroservicios.tiposidentificacion'));
        combo.getStore().load();

        combo=Ext.ComponentQuery.query('PanelRegistroServicios combobox[name=identificacion]')[0];
        combo.bindStore(Ext.create('siadno.store.registroservicios.personas'));
        var listConfig={
        loadingText: 'Buscando...',
        emptyText: 'No se encontraron personas.',               
        getInnerTpl: function() {
        return '<div>{abreviatura} {identificacion}</div><div>{nombres}</div>';                    
        }
        };
        combo.listConfig=listConfig;
        */
        /*
        this.textServicio=Ext.ComponentQuery.query('PanelRegistroServicios textfield[name=idservicio]')[0];
        this.matricula=Ext.ComponentQuery.query('PanelRegistroServicios textfield[name=matricula]')[0];

        combo=Ext.ComponentQuery.query('PanelRegistroServicios combobox[name=idtipoautomotor]')[0];
        combo.bindStore(Ext.create('siadno.store.registroservicios.tiposautomotor'));
        combo.getStore().load();
        */
    },

    traerServicios: function() {
        this.gridServicios.getStore().getProxy().extraParams.fechafinal=this.fechaFinal.getSubmitData().fechafinal;
        this.gridServicios.getStore().getProxy().extraParams.fechainicial=this.fechaInicial.getSubmitData().fechainicial;
        this.gridServicios.getStore().getProxy().extraParams.idestadoservicio=this.comboEstadosServicio.getValue();
        this.gridServicios.getStore().load();
    },

    traerPersona: function(idtipoidentificacion, identificacion) {
        var callback=function(){    
            this.fidservicio.setValue(this.idservicio);
            this.fidempleado.setValue(this.idempleado);
            this.fidpersona.setValue(this.idpersona);    
            this.fidestadoservicio.setValue(this.idestadoservicio);    
            this.ffechaentrega.setValue(this.fechaentrega);
            //this.ffechaentrega.setValue(new Date(this.fechaentrega));
            this.fhoraentrega.setValue(this.horaentrega);

        };
        siadno.enviarFormulario.call(this, true, 'PanelComplementoInf form[id=formServicio]',{accion:114, idtipoidentificacion:idtipoidentificacion, identificacion:identificacion}, callback, null);
    }

});