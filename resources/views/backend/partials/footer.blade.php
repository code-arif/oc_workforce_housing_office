<footer class="footer">
    <div class="container">
        <div class="row align-items-center flex-row-reverse">
            <div class="col-md-12 col-sm-12 text-center">
                {{ date('Y') }} &copy; <a href="{{ route('dashboard') }}" class="text-primary">{{ ucfirst(config('app.name')) }}</a>. All Right Reserved
            </div>
        </div>
    </div>
</footer>
