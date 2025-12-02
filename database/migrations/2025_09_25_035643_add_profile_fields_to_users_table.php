<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('avatar_url')->nullable()->after('email');
            $t->string('phone')->nullable()->after('avatar_url');
            $t->text('bio')->nullable()->after('phone');
            $t->json('socials')->nullable()->after('bio'); // {instagram:"", facebook:"", site:""}
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['avatar_url','phone','bio','socials']);
        });
    }
};

