

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>Erreur 404 - Page non trouvée</h4>
                </div>
                <div class="card-body">
                    <p>La page que vous recherchez n'existe pas.</p>
                    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/errors/404.blade.php ENDPATH**/ ?>