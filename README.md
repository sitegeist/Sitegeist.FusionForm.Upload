# Sitegeist.FusionForm.Upload

> Alternate implementations of upload fields for Fusion.Forms 

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## About

The package implements file uploads for Fusion.Forms with the main deviation that uploaded files are persisted in 
a cache and not as persistent resources. This has the advantage that resources do not have to manually cleaned and
are deleted after the configured cache period automatically.

To achieve this the package adds the classes `\Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile` which is a version
of the psr-uploaded file that has an additional cache-identifier. For uploading multiple files the collection
`\Sitegeist\FusionForm\Upload\Domain\CachedUploadedFileCollection` is added aswell. For both classed type converters 
are provided that ensure that values are cached and previously submitted values are restored for processing.

### Fusion prototype - `Sitegeist.FusionForm.Upload:Field.Upload`

#### Single file upload

The prototype `Sitegeist.FusionForm.Upload:Field.Upload` allows to render a single file field. 
It will usually be rendered in a field-container together with a rendering of previously uploaded files.
The submitted value should be interpreted as `\Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile` by the
receiving controller or the runtime form schema.

```neosfusion
<Neos.Fusion.Form:FieldContainer label="File" field.name="file">

    <!-- show imformations about previously uploaded files -->
    <Neos.Fusion:Fragment @if.has={Type.instance(field.getCurrentValue(), 'Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile')}>
        {field.getCurrentValue().clientFilename} ({field.getCurrentValue().clientMediaType}/{field.getCurrentValue().size})
    </Neos.Fusion:Fragment>

    <!-- the form field -->
    <Sitegeist.FusionForm.Upload:Field.Upload />

</Neos.Fusion.Form:FieldContainer>
```

The according schema runtime forms will likely look like this.

```neosfusion
file = ${SitegeistUpload.Schema.upload()}
requiredFile = ${SitegeistUpload.Schema.upload().isRequired()}
jpgFile = ${SitegeistUpload.Schema.upload().validator('Sitegeist.FusionForm.Upload:UploadedFile', {'allowedExtensions': ["jpg"]})}
```

#### Multio file upload

For multiple uploads the prototype `Sitegeist.FusionForm.Upload:Field.Upload` supports the `field.multiple` property.
It will usually be rendered in a field-container together with a rendering of previously uploaded files.
The submitted value should be interpreted as `\Sitegeist\FusionForm\Upload\Domain\CachedUploadedFileCollection` by the
receiving controller or the runtime form schema.

```neosfusion
<Neos.Fusion.Form:FieldContainer label="Files" field.name="files" field.multiple={true}>

    <!-- show imformations about previously uploaded files since we  have a multifield we habe to loop here-->
    <Neos.Fusion:Loop items={field.getCurrentValue()} itemName="item" @if={field.getCurrentValue()}>
        <Neos.Fusion:Fragment @if.has={Type.instance(item, 'Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile')}>
            {item.clientFilename} ({item.clientMediaType} / {item.size}) <br/>
        </Neos.Fusion:Fragment>
    </Neos.Fusion:Loop>
  
    <!-- render input field -->
    <Sitegeist.FusionForm.Upload:Field.Upload />
    
</Neos.Fusion.Form:FieldContainer>
```

The according schema runtime forms will likely look like this.

```neosfusion
files = ${SitegeistUpload.Schema.uploads()}
requiredFiles = ${SitegeistUpload.Schema.uploads().isRequired()}
jpgFiles = ${SitegeistUpload.Schema.uploads().validator('Sitegeist.FusionForm.Upload:UploadedFileCollection', {'allowedExtensions': ["jpg"]})}
```

## Attaching uploaded files in emails

Since the files implement the interface `\Psr\Http\Message\UploadedFileInterface` the existing Email Action
can already handle those types.

```neosfusion
email {
    type = 'Neos.Fusion.Form.Runtime:Email'
    options {
        attachments = Neos.Fusion:DataStructure {
          file = ${data.file} 
        }
    }
}
```
CachedUploadedFileCollection can be assigned directly to the mail attachments.
```neosfusion
email {
    type = 'Neos.Fusion.Form.Runtime:Email'
    options {
        attachments = ${data.files.asArray}
    }
}
```

## UploadedFile caching

The uploaded files are persisted in the `Sitegeist_FusionForm_Upload_UploadedFileCache` which allows to
configure altenate cache backends and the cache lifetime.

```yaml
Sitegeist_FusionForm_Upload_UploadedFileCache:
  frontend: Neos\Cache\Frontend\VariableFrontend
  backend: Neos\Cache\Backend\FileBackend
  backendOptions:
    defaultLifetime: 3600
```

```shell
./flow flow:cache:flushone Sitegeist_FusionForm_Upload_UploadedFileCache
```

## Installation

Sitegeist.Taxonomy is available via packagist run `composer require sitegeist/fusionform-upload` to install.

We use semantic-versioning so every breaking change will increase the major-version number.

## Contribution

We will gladly accept contributions. Please send us pull requests.

## License

See [LICENSE](LICENSE)
