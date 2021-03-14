<?php

namespace Tests\Generators;

use Tests\TestCase;

class ModelGeneratorTest extends TestCase
{
    /** @test */
    public function it_creates_correct_model_class_content()
    {
        config(['auth.providers.users.model' => 'App\Models\User']);
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $modelPath = app_path('Models/'.$this->model_name.'.php');
        $this->assertFileExists($modelPath);
        $modelClassContent = "<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$this->model_name} extends Model
{
    use HasFactory;

    protected \$fillable = ['title', 'description', 'creator_id'];

    public function getTitleLinkAttribute()
    {
        \$title = __('app.show_detail_title', [
            'title' => \$this->title, 'type' => __('{$this->lang_name}.{$this->lang_name}'),
        ]);
        \$link = '<a href=\"'.route('{$this->table_name}.show', \$this).'\"';
        \$link .= ' title=\"'.\$title.'\">';
        \$link .= \$this->title;
        \$link .= '</a>';

        return \$link;
    }

    public function creator()
    {
        return \$this->belongsTo(User::class);
    }
}
";
        $this->assertEquals($modelClassContent, file_get_contents($modelPath));
    }

    /** @test */
    public function it_creates_correct_namespaced_model_class_content()
    {
        config(['auth.providers.users.model' => 'App\Models\User']);
        $this->artisan('make:crud', ['name' => 'Entities/References/Category', '--no-interaction' => true]);

        $modelPath = app_path('Entities/References/Category.php');
        $this->assertFileExists($modelPath);
        $modelClassContent = "<?php

namespace App\Entities\References;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected \$fillable = ['title', 'description', 'creator_id'];

    public function getTitleLinkAttribute()
    {
        \$title = __('app.show_detail_title', [
            'title' => \$this->title, 'type' => __('category.category'),
        ]);
        \$link = '<a href=\"'.route('categories.show', \$this).'\"';
        \$link .= ' title=\"'.\$title.'\">';
        \$link .= \$this->title;
        \$link .= '</a>';

        return \$link;
    }

    public function creator()
    {
        return \$this->belongsTo(User::class);
    }
}
";
        $this->assertEquals($modelClassContent, file_get_contents($modelPath));

        // tearDown
        $this->removeFileOrDir(resource_path('views/categories'));
        $this->removeFileOrDir(resource_path("lang/en/category.php"));
    }

    /** @test */
    public function it_doesnt_override_the_existing_model()
    {
        $this->mockConsoleOutput = true;
        config(['auth.providers.users.model' => 'App\Models\User']);
        $this->artisan('make:model', ['name' => 'Models/'.$this->model_name, '--no-interaction' => true]);
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true])
            ->expectsQuestion('Model file exists, are you sure to generate CRUD files?', true);

        $modelPath = app_path('Models/'.$this->model_name.'.php');
        $this->assertFileExists($modelPath);
        $modelClassContent = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$this->model_name} extends Model
{
    use HasFactory;
}
";
        $this->assertEquals($modelClassContent, file_get_contents($modelPath));
    }
}
