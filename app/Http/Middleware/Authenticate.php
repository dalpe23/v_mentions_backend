    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        parent::unauthenticated($request, $guards);
    }