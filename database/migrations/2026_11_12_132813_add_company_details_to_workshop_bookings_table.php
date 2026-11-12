<?php

use App\Enums\CompanyRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshop_bookings', function (Blueprint $table) {
            $table->string('company_role')->default(CompanyRole::Developer->value)->after('workshop_id');
            $table->string('company_name')->nullable()->after('company_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_bookings', function (Blueprint $table) {
            $table->dropColumn(['company_role', 'company_name']);
        });
    }
};
