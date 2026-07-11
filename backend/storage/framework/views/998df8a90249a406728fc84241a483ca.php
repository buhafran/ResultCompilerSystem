<?php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();
    $length = $getLength();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
?>

<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $fieldWrapperView] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => $field]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div
        x-data="{
            code: $wire.<?php echo e($applyStateBindingModifiers("\$entangle('{$statePath}')")); ?>,
        }"
    >
        <?php if (isset($component)) { $__componentOriginaldebbf61cee98a39c5dcc4cd408616766 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldebbf61cee98a39c5dcc4cd408616766 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.input.one-time-code','data' => ['xModel' => 'code','length' => $length,'attributes' => 
                \Filament\Support\prepare_inherited_attributes(
                    $getExtraAttributeBag()->merge($getExtraAlpineAttributes(), escape: false),
                )
            ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::input.one-time-code'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'code','length' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($length),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                \Filament\Support\prepare_inherited_attributes(
                    $getExtraAttributeBag()->merge($getExtraAlpineAttributes(), escape: false),
                )
            )]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('input', null, ['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                    \Filament\Support\prepare_inherited_attributes(
                        $getExtraInputAttributeBag()->merge([
                            'autofocus' => $isAutofocused(),
                            'disabled' => $isDisabled,
                            'id' => $getId(),
                            'readonly' => $isReadOnly(),
                            'required' => $isRequired() && (! $isConcealed),
                        ], escape: false),
                    )
                )]); ?>  <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldebbf61cee98a39c5dcc4cd408616766)): ?>
<?php $attributes = $__attributesOriginaldebbf61cee98a39c5dcc4cd408616766; ?>
<?php unset($__attributesOriginaldebbf61cee98a39c5dcc4cd408616766); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldebbf61cee98a39c5dcc4cd408616766)): ?>
<?php $component = $__componentOriginaldebbf61cee98a39c5dcc4cd408616766; ?>
<?php unset($__componentOriginaldebbf61cee98a39c5dcc4cd408616766); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH /Users/buhafran/iCloud Drive (Archive)/Documents/Code/ResultSystem-2/backend/vendor/filament/forms/resources/views/components/one-time-code-input.blade.php ENDPATH**/ ?>