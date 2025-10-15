/**
 * Product Master Module JavaScript
 */

(function($) {
    'use strict';

    var ProductMaster = {
        init: function() {
            this.initDataTables();
            this.initFormValidation();
            this.initEventHandlers();
            this.initTooltips();
            this.initSearchFilters();
        },

        initDataTables: function() {
            // Initialize DataTables for all tables
            $('.table-groups, .table-subgroups, .table-categories, .table-subcategories, .table-units, .table-multi-units').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }
                
                $(this).DataTable({
                    responsive: true,
                    language: {
                        search: '',
                        searchPlaceholder: 'Search...',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            first: '<i class="fa fa-angle-double-left"></i>',
                            previous: '<i class="fa fa-angle-left"></i>',
                            next: '<i class="fa fa-angle-right"></i>',
                            last: '<i class="fa fa-angle-double-right"></i>'
                        }
                    },
                    pageLength: 25,
                    order: [[1, 'asc']],
                    columnDefs: [
                        {
                            targets: [-1],
                            orderable: false,
                            searchable: false
                        }
                    ]
                });
            });
        },

        initFormValidation: function() {
            // Form validation for all forms
            $('form[id$="_form"]').each(function() {
                $(this).validate({
                    errorClass: 'text-danger',
                    errorElement: 'small',
                    highlight: function(element) {
                        $(element).closest('.form-group').addClass('has-error');
                    },
                    unhighlight: function(element) {
                        $(element).closest('.form-group').removeClass('has-error');
                    },
                    submitHandler: function(form) {
                        ProductMaster.submitForm(form);
                    }
                });
            });
        },

        submitForm: function(form) {
            var $form = $(form);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.text();
            
            // Disable submit button and show loading
            $submitBtn.prop('disabled', true)
                     .html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            // Submit form via AJAX
            $.post($form.attr('action'), $form.serialize())
                .done(function(response) {
                    if (response.success) {
                        ProductMaster.showAlert('success', response.message || 'Operation completed successfully');
                        $form.closest('.modal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        ProductMaster.showAlert('danger', response.message || 'An error occurred');
                    }
                })
                .fail(function() {
                    ProductMaster.showAlert('danger', 'An error occurred while processing your request');
                })
                .always(function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                });
        },

        initEventHandlers: function() {
            // Modal reset on hide
            $('.modal').on('hidden.bs.modal', function() {
                var $modal = $(this);
                var $form = $modal.find('form');
                
                $form[0].reset();
                $form.find('input[name="id"]').val('');
                $form.find('.has-error').removeClass('has-error');
                $form.find('.text-danger').remove();
                $form.find('.selectpicker').selectpicker('refresh');
                
                $modal.find('.add-title').show();
                $modal.find('.edit-title').hide();
            });

            // Cascade dropdowns
            this.initCascadeDropdowns();

            // Delete confirmation
            $(document).on('click', '._delete', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                ProductMaster.confirmDelete(url);
            });

            // Auto-generate codes
            $(document).on('change', 'input[name$="_name"]', function() {
                var name = $(this).val();
                var codeField = $(this).closest('form').find('input[name$="_code"]');
                if (codeField.val() === '') {
                    var code = ProductMaster.generateCode(name);
                    codeField.val(code);
                }
            });
        },

        initCascadeDropdowns: function() {
            // Group to Subgroup cascade
            $(document).on('change', 'select[name="group_id"]', function() {
                var groupId = $(this).val();
                var $subgroupSelect = $(this).closest('form').find('select[name="subgroup_id"]');
                
                if (groupId) {
                    ProductMaster.loadSubgroups(groupId, $subgroupSelect);
                } else {
                    $subgroupSelect.empty().append('<option value="">Select Subgroup</option>').selectpicker('refresh');
                }
            });

            // Subgroup to Category cascade
            $(document).on('change', 'select[name="subgroup_id"]', function() {
                var subgroupId = $(this).val();
                var $categorySelect = $(this).closest('form').find('select[name="category_id"]');
                
                if (subgroupId) {
                    ProductMaster.loadCategories(subgroupId, $categorySelect);
                } else {
                    $categorySelect.empty().append('<option value="">Select Category</option>').selectpicker('refresh');
                }
            });

            // Category to Subcategory cascade
            $(document).on('change', 'select[name="category_id"]', function() {
                var categoryId = $(this).val();
                var $subcategorySelect = $(this).closest('form').find('select[name="subcategory_id"]');
                
                if (categoryId) {
                    ProductMaster.loadSubcategories(categoryId, $subcategorySelect);
                } else if ($subcategorySelect.length) {
                    $subcategorySelect.empty().append('<option value="">Select Sub Category</option>').selectpicker('refresh');
                }
            });
        },

        loadSubgroups: function(groupId, $select) {
            $.get(admin_url + 'product_master/get_subgroups_by_group/' + groupId)
                .done(function(data) {
                    var subgroups = JSON.parse(data);
                    $select.empty().append('<option value="">Select Subgroup</option>');
                    
                    $.each(subgroups, function(i, subgroup) {
                        $select.append('<option value="' + subgroup.id + '">' + subgroup.subgroup_name + '</option>');
                    });
                    
                    $select.selectpicker('refresh');
                })
                .fail(function() {
                    ProductMaster.showAlert('danger', 'Failed to load subgroups');
                });
        },

        loadCategories: function(subgroupId, $select) {
            $.get(admin_url + 'product_master/get_categories_by_subgroup/' + subgroupId)
                .done(function(data) {
                    var categories = JSON.parse(data);
                    $select.empty().append('<option value="">Select Category</option>');
                    
                    $.each(categories, function(i, category) {
                        $select.append('<option value="' + category.id + '">' + category.category_name + '</option>');
                    });
                    
                    $select.selectpicker('refresh');
                })
                .fail(function() {
                    ProductMaster.showAlert('danger', 'Failed to load categories');
                });
        },

        loadSubcategories: function(categoryId, $select) {
            $.get(admin_url + 'product_master/get_subcategories_by_category/' + categoryId)
                .done(function(data) {
                    var subcategories = JSON.parse(data);
                    $select.empty().append('<option value="">Select Sub Category</option>');
                    
                    $.each(subcategories, function(i, subcategory) {
                        $select.append('<option value="' + subcategory.id + '">' + subcategory.subcategory_name + '</option>');
                    });
                    
                    $select.selectpicker('refresh');
                })
                .fail(function() {
                    ProductMaster.showAlert('danger', 'Failed to load sub categories');
                });
        },

        generateCode: function(name) {
            if (!name) return '';
            
            // Convert to uppercase and remove special characters
            var code = name.toUpperCase()
                          .replace(/[^A-Z0-9\s]/g, '')
                          .replace(/\s+/g, '')
                          .substring(0, 10);
            
            return code;
        },

        confirmDelete: function(url) {
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                window.location.href = url;
            }
        },

        showAlert: function(type, message) {
            var alertClass = 'alert-' + type;
            var iconClass = type === 'success' ? 'fa-check-circle' : 
                           type === 'danger' ? 'fa-exclamation-triangle' : 
                           type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            var alert = '<div class="alert ' + alertClass + ' alert-dismissible fade in" role="alert">' +
                       '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                       '<span aria-hidden="true">&times;</span>' +
                       '</button>' +
                       '<i class="fa ' + iconClass + '"></i> ' + message +
                       '</div>';
            
            $('.content').prepend(alert);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        },

        initTooltips: function() {
            $('[data-toggle="tooltip"]').tooltip();
        },

        initSearchFilters: function() {
            // Global search functionality
            $('.pm-global-search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                var $table = $(this).closest('.panel-body').find('table');
                
                $table.find('tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Status filter
            $('.pm-status-filter').on('change', function() {
                var status = $(this).val();
                var $table = $(this).closest('.panel-body').find('table');
                
                if (status === '') {
                    $table.find('tbody tr').show();
                } else {
                    $table.find('tbody tr').each(function() {
                        var rowStatus = $(this).find('.label').text().toLowerCase();
                        if (status === 'active' && rowStatus.includes('active')) {
                            $(this).show();
                        } else if (status === 'inactive' && rowStatus.includes('inactive')) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });
        },

        // Utility functions
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

        validateCode: function(code) {
            // Basic code validation
            var pattern = /^[A-Z0-9]+$/;
            return pattern.test(code) && code.length >= 2;
        },

        // Unit conversion calculator
        calculateConversion: function(fromValue, conversionRate) {
            return (parseFloat(fromValue) * parseFloat(conversionRate)).toFixed(6);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ProductMaster.init();
    });

    // Make ProductMaster available globally
    window.ProductMaster = ProductMaster;

})(jQuery);